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
from problem_generator.stage4_classification.subject_classifier import (
    OpenAISubjectClassifier,
)
from problem_generator.stage5_problem_generation.problem_generator import (
    MathProblemGenerator,
    KoreanProblemGenerator,
    EnglishProblemGenerator,
    ScienceProblemGenerator,
)
from problem_generator.stage7_export.pdf_exporter import ReportlabPdfExporter
import shutil
from langchain_openai import ChatOpenAI
from chatbot.claude import run_claude

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

# 세션 데이터 저장 (메모리)
sessions = {}


@app.post("/generate_problems/")
async def generate_problems(
    file: UploadFile = File(...),
    subject: str = Form(...),
    grade: str = Form(...),
    num_problems: int = Form(...),
    prompt: str = Form(None),
):
    # 1. 파일 임시 저장
    file_id = str(uuid.uuid4())
    file_path = os.path.join(UPLOAD_DIR, f"{file_id}.pdf")
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    # 2. PDF 로드
    pages = load_pdf(file_path)

    # 3. 문서 청킹
    chunker = RecursiveChunker()
    chunks = chunker.chunk(pages)

    # 4. 벡터스토어 구축
    vector_store = FaissVectorStoreAdapter()
    store = vector_store.from_documents(chunks)

    # 5. 과목 분류 및 생성기 선택
    classifier = OpenAISubjectClassifier()
    classified_subject = classifier.classify(file_path, pages)
    try:
        generator = classifier.get_problem_generator(classified_subject)
    except ValueError:
        return JSONResponse(
            status_code=400, content={"error": "지원하지 않는 과목입니다."}
        )

    # 6. 문제 생성
    retriever = vector_store.as_retriever(store)
    problems = generator.generate(retriever, prompt or "")

    session_id = str(uuid.uuid4())
    sessions[session_id] = {
        "history": [
            {"role": "user", "content": prompt or ""},
            {"role": "system", "content": str(problems)},
        ],
        "problems": problems,
    }
    return {"session_id": session_id, "problems": problems}


@app.post("/edit_problems/")
async def edit_problems(
    session_id: str = Body(...),
    user_edit: str = Body(...),
):
    session = sessions.get(session_id)

    if not session:
        return JSONResponse(
            status_code=404, content={"error": "세션을 찾을 수 없습니다."}
        )

    history = session["history"]
    problems = session["problems"]

    history.append({"role": "user", "content": user_edit})

    # LLM 기반 문제 수정 로직
    llm = ChatOpenAI(model="gpt-4o", temperature=0.7)

    with open(os.path.join(PROMPTS_DIR, "edit_problem.txt"), encoding="utf-8") as f:
        prompt_template = f.read()

    prompt = prompt_template.format(problems=problems, user_edit=user_edit)
    response = llm.invoke(prompt)

    new_problems = response.content if hasattr(response, "content") else response

    history.append({"role": "system", "content": str(new_problems)})
    session["problems"] = new_problems

    return {"session_id": session_id, "problems": new_problems, "history": history}


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
    return {"result": result}


# FastAPI 앱 실행 (uvicorn 사용)
if __name__ == "__main__":
    import uvicorn

    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
