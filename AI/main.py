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
from fastapi.responses import FileResponse, JSONResponse
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


@app.post("/edit_problems/")
async def edit_problems(
    messages: List[Dict[str, str]] = Body(...),
    user_edit: str = Body(...),
):
    # LLM 기반 문제 수정 로직
    llm = ChatOpenAI(model="gpt-4o", temperature=0.7)

    with open(os.path.join(PROMPTS_DIR, "edit_problem.txt"), encoding="utf-8") as f:
        prompt_template = f.read()

    prompt = prompt_template.format(messages=messages, user_edit=user_edit)
    response = llm.invoke(prompt)

    new_problems = response.content if hasattr(response, "content") else response

    return {"problems": new_problems}


@app.post("/export_pdf/")
async def export_pdf(problems: list = Body(...)):
    file_id = str(uuid.uuid4())
    export_path = os.path.join(EXPORT_DIR, f"{file_id}.pdf")

    exporter = ReportlabPdfExporter()
    exporter.export(problems, export_path)

    return FileResponse(
        export_path, media_type="application/pdf", filename=f"problems_{file_id}.pdf"
    )


@app.post("/run_claude/")
async def run_claude_api(
    system_prompt: str = Body(...),
    user_prompt: str = Body(...),
):
    """
    Claude LLM을 실행하는 API 엔드포인트
    """
    result = run_claude(system_prompt, user_prompt)
    session_id = str(uuid.uuid4())
    return {"result": result, "session_id": session_id}


@app.post("/run_prompt/")
async def run_prompt(
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
    )

    return {"result": response.output_text}


# FastAPI 앱 실행 (uvicorn 사용)
if __name__ == "__main__":
    import uvicorn

    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
