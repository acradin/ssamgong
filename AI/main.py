"""
@file        main.py
@author      MinJun Park
@date        2025-04-30
@description FastAPI 기반의 문제 생성 서버
             PDF 파일로부터 문제를 생성하고, 편집, 내보내기 기능을 제공하는 메인 서버 애플리케이션

@update-log
"""

import os
from dotenv import load_dotenv
from fastapi import FastAPI, Body, UploadFile, File, Form
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import uuid
from problem_generator.stage1_pdf_load.pdf_loader import load_pdf
from problem_generator.stage2_chunking.document_chunker import RecursiveChunker
from problem_generator.stage3_vector_store.vector_store import (
    FaissVectorStoreAdapter,
)
from problem_generator.stage5_problem_generation.problem_generator import (
    MathProblemGenerator,
    KoreanProblemGenerator,
    EnglishProblemGenerator,
    ScienceProblemGenerator,
    EtcProblemGenerator,
)
from problem_generator.stage7_export.pdf_exporter import ReportlabPdfExporter
import shutil
from langchain_openai import ChatOpenAI
from chatbot.claude import run_claude
from openai import OpenAI
from typing import List, Dict
from problem_generator.uitls.llm_parser import parse_llm_response

# FastAPI 앱 생성
app = FastAPI()

# CORS 설정
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 환경 변수 로드
load_dotenv()

# 기본 디렉토리 설정
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
UPLOAD_DIR = os.path.join(BASE_DIR, "uploads")
EXPORT_DIR = os.path.join(BASE_DIR, "exports")
PROMPTS_DIR = os.path.join(BASE_DIR, "AI", "problem_generator", "prompts")

os.makedirs(UPLOAD_DIR, exist_ok=True)
os.makedirs(EXPORT_DIR, exist_ok=True)


@app.post("/generate_problems/")
async def generate_problems(
    file: UploadFile = File(...),
    subject: str = Form(...),
    school_level: str = Form(...),
    grade: str = Form(...),
    exam_type: str = Form(...),
    num_problems: int = Form(...),
    difficulty: str = Form(...),
    problem_type: str = Form(...),
    additional_prompt: str = Form(None),
):
    """
    PDF 파일과 다양한 조건(과목, 학년, 난이도 등)을 받아 OpenAI GPT-4.1을 통해 문제를 생성하는 API입니다.

    동작 순서:
        1. 업로드된 PDF 파일을 OpenAI에 업로드하고 file_id를 발급받습니다.
        2. 과목에 따라 적절한 프롬프트 파일을 선택하여 템플릿을 불러옵니다.
        3. 입력받은 조건(학년, 난이도 등)으로 프롬프트를 완성합니다.
        4. user 메시지(추가 프롬프트 또는 기본 메시지)와 파일을 함께 LLM에 전달합니다.
        5. GPT-4.1의 응답을 파싱하여 [대답] 섹션과 그 이후 섹션을 분리해 반환합니다.
        6. 결과에 session_id를 추가하여 반환합니다.

    Args:
        file (UploadFile): 문제 생성을 위한 PDF 파일
        subject (str): 과목명
        school_level (str): 학교 단계
        grade (str): 학년
        exam_type (str): 시험 유형
        num_problems (int): 생성할 문제 개수
        difficulty (str): 난이도
        problem_type (str): 문제 유형
        additional_prompt (str, optional): 추가 프롬프트

    Returns:
        dict: {
            "conversation": [대답] 섹션 내용 또는 에러 메시지,
            "content": [대답] 이후 섹션 내용 또는 에러 메시지,
            "session_id": 세션 식별자
        }
    """
    # OpenAI 클라이언트 초기화
    client = OpenAI()

    # 비동기 방식 (FastAPI에서 권장)
    file_content = await file.read()
    openai_file = client.files.create(
        file=(file.filename, file_content, file.content_type), purpose="assistants"
    )
    file_id = openai_file.id

    # 프롬프트 파일명 결정 및 읽기
    if subject == "수학":
        prompt_file = "math_problem.txt"
    elif subject == "국어":
        prompt_file = "korean_problem.txt"
    elif subject == "영어":
        prompt_file = "english_problem.txt"
    elif subject == "과학":
        prompt_file = "science_problem.txt"
    else:
        prompt_file = "etc_problem.txt"

    with open(os.path.join(PROMPTS_DIR, prompt_file), encoding="utf-8") as f:
        prompt_template = f.read()

    # 프롬프트 완성
    variables = {
        "context": "없음",
        "school_level": school_level,
        "grade": grade,
        "subject": subject,
        "exam_type": exam_type,
        "num_problems": num_problems,
        "difficulty": difficulty,
        "problem_type": problem_type,
        "additional_prompt": additional_prompt or "없음",
    }

    system_prompt = prompt_template.format(**variables)

    # user 메시지 텍스트 결정
    user_text = (
        additional_prompt
        if additional_prompt
        else f"주어진 PDF 파일을 분석하여 {subject} 문제를 생성해주세요."
    )

    session_id = str(uuid.uuid4())

    # GPT-4.1 호출
    response = client.responses.create(
        model="gpt-4.1",
        input=[
            {"role": "system", "content": system_prompt},
            {
                "role": "user",
                "content": [
                    {"type": "input_text", "text": user_text},
                    {"type": "input_file", "file_id": file_id},
                ],
            },
        ],
        stream=False,
    )

    result = parse_llm_response(response.output_text)
    result["session_id"] = session_id

    return result


@app.post("/edit_problems/")
async def edit_problems(
    messages: List[Dict[str, str]] = Body(...),
    user_edit: str = Body(...),
    file_id: str = Body(""),
):
    """
    기존 문제(또는 대화 이력)와 사용자의 편집 요청, 그리고 (선택적으로) 파일을 받아
    LLM(GPT-4.1)을 통해 문제를 자연스럽게 수정하는 API입니다.

    동작 순서:
        1. 프롬프트 템플릿을 불러와 사용자 입력(messages, user_edit)으로 완성합니다.
        2. file_id가 전달된 경우, 해당 파일이 실제로 OpenAI에 존재하는지 확인합니다.
        3. 파일이 존재하면 input에 파일 정보를 추가하고, 아니면 텍스트만 전달합니다.
        4. GPT-4.1 모델에 system/user 역할로 입력을 전달하여 문제 수정 결과를 요청합니다.
        5. LLM의 응답을 파싱하여 [대답] 섹션과 그 이후 섹션을 분리해 반환합니다.

    Args:
        messages (List[Dict[str, str]]): 기존 대화/문제 이력
        user_edit (str): 사용자의 편집 요청
        file_id (str, optional): OpenAI에 업로드된 파일 ID (없으면 빈 문자열)

    Returns:
        dict: {
            "conversation": [대답] 섹션 내용 또는 에러 메시지,
            "content": [대답] 이후 섹션 내용 또는 에러 메시지
        }
    """
    # LLM 기반 문제 수정 로직
    client = OpenAI()

    with open(os.path.join(PROMPTS_DIR, "edit_problem.txt"), encoding="utf-8") as f:
        prompt_template = f.read()

    prompt = prompt_template.format(messages=messages, user_edit=user_edit)

    # file_id가 실제로 OpenAI에 존재하는지 확인
    file_exists = False
    if file_id:
        try:
            client.files.retrieve(file_id)
            file_exists = True
        except Exception:
            file_exists = False

    user_content = [{"type": "input_text", "text": prompt}]
    if file_exists:
        user_content.append({"type": "input_file", "file_id": file_id})
    # GPT-4.1 호출
    response = client.responses.create(
        model="gpt-4.1",
        input=[
            {
                "role": "system",
                "content": "너는 일반적인 글을 편집하는 전문 편집자야. 사용자의 대화 내용과 기존 글을 참고해서, 사용자의 요청에 따라 글 전체를 자연스럽게 수정해야 해.",
            },
            {
                "role": "user",
                "content": user_content,
            },
        ],
        stream=False,
    )

    result_text = getattr(response, "output_text", str(response))

    return parse_llm_response(result_text)


@app.post("/run_claude/")
async def run_claude_api(
    system_prompt: str = Body(...),
    user_prompt: str = Body(...),
):
    """
    Claude LLM을 실행하여 시스템 프롬프트와 사용자 프롬프트를 기반으로 결과를 반환하는 API입니다.

    동작 순서:
        1. 시스템 프롬프트 템플릿(claude_system.txt)을 불러와 입력받은 system_prompt와 합칩니다.
        2. 완성된 시스템 프롬프트와 user_prompt를 Claude LLM에 전달하여 결과를 요청합니다.
        3. Claude LLM의 응답을 파싱하여 [대답] 섹션과 그 이후 섹션을 분리해 반환합니다.
        4. 결과에 session_id를 추가하여 반환합니다.

    Args:
        system_prompt (str): Claude LLM에 전달할 시스템 프롬프트(지시문)
        user_prompt (str): Claude LLM에 전달할 사용자 프롬프트(질문/요청)

    Returns:
        dict: {
            "conversation": [대답] 섹션 내용 또는 에러 메시지,
            "content": [대답] 이후 섹션 내용 또는 에러 메시지,
            "session_id": 세션 식별자
        }
    """
    with open(os.path.join(PROMPTS_DIR, "claude_system.txt"), encoding="utf-8") as f:
        format_system_prompt = f.read()

    # 프롬프트로 출력 형식 지정
    total_system_prompt = format_system_prompt + "\n\n" + system_prompt

    # 클로드 실행
    result = run_claude(total_system_prompt, user_prompt)

    # 결과
    result = parse_llm_response(result)
    session_id = str(uuid.uuid4())
    result["session_id"] = session_id
    return result


@app.post("/edit_chats/")
async def edit_problems(
    messages: List[Dict[str, str]] = Body(...),
    user_edit: str = Body(...),
):
    """
    기존 대화 이력(messages)과 사용자의 편집 요청(user_edit)을 받아
    LLM(GPT-4.1)을 통해 대화 내용을 자연스럽게 수정하는 API입니다.

    동작 순서:
        1. 프롬프트 템플릿(edit_chat.txt)을 불러와 사용자 입력(messages, user_edit)으로 완성합니다.
        2. 완성된 프롬프트를 LLM(ChatOpenAI)에 전달하여 대화 수정 결과를 요청합니다.
        3. LLM의 응답을 파싱하여 [대답] 섹션과 그 이후 섹션을 분리해 반환합니다.

    Args:
        messages (List[Dict[str, str]]): 기존 대화 이력
        user_edit (str): 사용자의 편집 요청

    Returns:
        dict: {
            "conversation": [대답] 섹션 내용 또는 에러 메시지,
            "content": [대답] 이후 섹션 내용 또는 에러 메시지
        }
    """
    # LLM 기반 문제 수정 로직
    llm = ChatOpenAI(model="gpt-4.1", temperature=0.7)

    with open(os.path.join(PROMPTS_DIR, "edit_chat.txt"), encoding="utf-8") as f:
        prompt_template = f.read()

    prompt = prompt_template.format(messages=messages, user_edit=user_edit)

    result = llm.invoke(prompt)

    return parse_llm_response(result.content)


@app.post("/generate_problems_with_rag/")
async def generate_problems_with_rag(
    file: UploadFile = File(...),
    subject: str = Form(...),
    school_level: str = Form(...),
    grade: str = Form(...),
    exam_type: str = Form(...),
    num_problems: int = Form(...),
    difficulty: str = Form(...),
    problem_type: str = Form(...),
    additional_prompt: str = Form(None),
):
    """
    PDF 파일을 업로드 받아 과목, 학년, 난이도 등 다양한 조건에 맞는 문제를 자동으로 생성하는 API입니다.

    동작 순서:
        1. 업로드된 PDF 파일을 임시로 저장합니다.
        2. PDF 파일을 읽어 각 페이지를 추출합니다.
        3. 문서를 청킹(chunking)하여 작은 단위로 분할합니다.
        4. 분할된 문서로 벡터스토어를 구축합니다.
        5. 과목에 따라 적절한 문제 생성기를 선택합니다.
        6. 벡터스토어에서 관련 문서를 검색하여 문제 생성을 위한 context를 준비합니다.
        7. 입력받은 조건(학년, 난이도 등)과 context를 바탕으로 문제를 생성합니다.
        8. 세션 ID와 생성된 문제 리스트를 반환합니다.
        9. (finally) 임시로 저장한 파일을 삭제합니다.

    Args:
        file (UploadFile): 문제 생성을 위한 PDF 파일
        subject (str): 과목명 (예: 수학, 국어 등)
        school_level (str): 학교 단계 (예: 초등, 중등, 고등)
        grade (str): 학년
        exam_type (str): 시험 유형
        num_problems (int): 생성할 문제 개수
        difficulty (str): 난이도
        problem_type (str): 문제 유형
        additional_prompt (str, optional): 추가 프롬프트

    Returns:
        dict: {
            "session_id": 세션 식별자,
            "problems": 생성된 문제 리스트
        }
    """
    # 1. 파일 임시 저장
    file_id = str(uuid.uuid4())
    file_path = os.path.join(UPLOAD_DIR, f"{file_id}.pdf")
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    try:
        # 2. PDF 로드
        pages = load_pdf(file_path)

        # 3. 문서 청킹
        chunker = RecursiveChunker()
        chunks = chunker.chunk(pages)

        # 4. 벡터스토어 구축
        vector_store = FaissVectorStoreAdapter()
        store = vector_store.from_documents(chunks)

        # 5. 과목에 따른 생성기 선택
        generators = {
            "수학": MathProblemGenerator(),
            "국어": KoreanProblemGenerator(),
            "영어": EnglishProblemGenerator(),
            "과학": ScienceProblemGenerator(),
            "기타": EtcProblemGenerator(),
        }

        generator = generators.get(subject, generators["기타"])
        if not generator:
            return JSONResponse(
                status_code=400,
                content={"error": f"예상치 못한 에러가 발생했습니다"},
            )

        # 6. 문제 생성
        retriever = vector_store.as_retriever(store)
        variables = {
            "context": "",  # retriever에서 관련 문서를 찾아 채워질 것임
            "school_level": school_level,
            "grade": grade,
            "subject": subject,
            "exam_type": exam_type,
            "num_problems": num_problems,
            "difficulty": difficulty,
            "problem_type": problem_type,
            "additional_prompt": additional_prompt or "없음",
        }
        result = generator.generate(retriever, variables)

        print(f"problems : {result}")

        session_id = str(uuid.uuid4())

        return {"session_id": session_id, "problems": result}

    finally:
        # 임시 파일 삭제
        if os.path.exists(file_path):
            os.remove(file_path)


# FastAPI 앱 실행 (uvicorn 사용)
if __name__ == "__main__":
    import uvicorn

    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
