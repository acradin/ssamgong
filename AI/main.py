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

UPLOAD_DIR = "uploads"
EXPORT_DIR = "exports"
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
    vector_store.build(chunks)
    # 5. 과목 분류
    classifier = OpenAISubjectClassifier()
    classified_subject = classifier.classify(subject, chunks)
    # 6. 문제 생성기 선택
    if classified_subject == "수학":
        generator = MathProblemGenerator()
    elif classified_subject == "국어":
        generator = KoreanProblemGenerator()
    elif classified_subject == "영어":
        generator = EnglishProblemGenerator()
    elif classified_subject == "과학":
        generator = ScienceProblemGenerator()
    else:
        return JSONResponse(
            status_code=400, content={"error": "지원하지 않는 과목입니다."}
        )
    # 7. 문제 생성
    retriever = vector_store.as_retriever()
    if classified_subject == "수학":
        problems = generator.generate(retriever, prompt or "")
    else:
        problems = generator.generate(retriever)
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
    with open("AI/problem_generator/prompts/edit_problem.txt", encoding="utf-8") as f:
        prompt_template = f.read()
    prompt = prompt_template.format(problems=problems, user_edit=user_edit)
    response = llm.invoke(prompt)
    new_problems = response.content if hasattr(response, 'content') else response
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


# FastAPI 앱 실행 (uvicorn 사용)
if __name__ == "__main__":
    import uvicorn

    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
