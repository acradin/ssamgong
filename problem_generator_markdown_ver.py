import os
import base64 
from langchain_openai import OpenAIEmbeddings
from langchain_community.document_loaders import PyPDFLoader
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import FAISS
from langchain_openai import ChatOpenAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
from dotenv import load_dotenv
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

# API 키 설정 (.env 파일에서 로드, 실제 코드에서는 직접 값을 넣지 않는 것이 좋음)
load_dotenv()
os.environ["OPENAI_API_KEY"] = "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"  # 보안을 위해 .env 파일이나 환경변수에서 로드하는 것이 좋음

# PDF 로더 설정
def load_pdf(pdf_path):
    try:
        loader = PyPDFLoader(pdf_path)
        pages = loader.load()
        print(f"PDF에서 {len(pages)}페이지를 로드했습니다.")
        return pages
    except Exception as e:
        print(f"PDF 로드 중 오류 발생: {e}")
        print("이미지 기반 PDF일 수 있습니다. 이미지 추출 방식으로 전환합니다.")
        return None

# 문서 청킹
def chunk_documents(documents):
    if not documents:
        return []
        
    text_splitter = RecursiveCharacterTextSplitter(
        chunk_size=1000,
        chunk_overlap=100,
        separators=["\n\n", "\n", " ", ""]
    )
    chunks = text_splitter.split_documents(documents)
    print(f"문서를 {len(chunks)}개 청크로 분할했습니다.")
    return chunks

# 임베딩 및 벡터 DB 저장
def create_vector_db(chunks):
    if not chunks:
        print("처리할 청크가 없습니다.")
        return None
        
    embeddings = OpenAIEmbeddings()
    vector_store = FAISS.from_documents(chunks, embeddings)
    print("벡터 데이터베이스 생성 완료")
    return vector_store

# 검색 기능
def get_retriever(vector_store):
    if not vector_store:
        return None
    return vector_store.as_retriever(search_kwargs={"k": 5})

# 질문 리프레이밍 함수
def reframe_question(question):
    llm = ChatOpenAI(model="gpt-4o", temperature=0)
    
    prompt = PromptTemplate(
        template="""당신은 학습 문서에서 정보를 찾는 전문가입니다.
        PDF 문서 전체 내용을 기반으로 문제를 출제하기 위해, 효과적인 검색 쿼리로 리프레이밍해주세요.
        원래 질문: {question}
        
        PDF 문서의 핵심 개념, 이론, 중요 내용을 모두 포함하는 포괄적인 검색 쿼리를 만들어주세요.
        
        검색 쿼리:""",
        input_variables=["question"]
    )
    
    chain = prompt | llm
    result = chain.invoke({"question": question})
    return result.content

# 이미지 기반 PDF에서 텍스트 추출 함수
def extract_text_from_image_pdf(pdf_path):
    try:
        # 이미지 PDF를 바이트 형태로 읽기
        with open(pdf_path, "rb") as file:
            pdf_bytes = file.read()
        
        # base64 인코딩
        base64_pdf = base64.b64encode(pdf_bytes).decode('utf-8')
        
        # GPT-4 Vision을 이용한 텍스트 추출
        vision_model = ChatOpenAI(model="gpt-4o", temperature=0)
        
        messages = [
            {"role": "user", "content": [
                {"type": "text", "text": "이 PDF에서 텍스트 내용을 추출해주세요. 가능한 한 구조를 유지하며 추출해주세요."},
                {"type": "image_url", "image_url": {"url": f"data:application/pdf;base64,{base64_pdf}"}}
            ]}
        ]
        
        response = vision_model.invoke(messages)
        print("이미지 기반 PDF에서 텍스트 추출 완료")
        return response.content
    except Exception as e:
        print(f"이미지 PDF 처리 중 오류 발생: {e}")
        return ""

# PDF 처리 통합 함수 (일반 PDF와 이미지 PDF 모두 처리)
def process_pdf(pdf_path):
    # 일반 PDF 처리 시도
    documents = load_pdf(pdf_path)
    
    # 일반 PDF 로딩에 실패하면 이미지 PDF로 처리
    if not documents:
        extracted_text = extract_text_from_image_pdf(pdf_path)
        
        # 추출된 텍스트를 문서로 변환
        from langchain_core.documents import Document
        documents = [Document(page_content=extracted_text)]
        print("이미지 PDF에서 추출한 텍스트로 문서 생성 완료")
    
    return documents

# 문제 생성 함수
def generate_questions(pdf_path, num_multiple_choice=5, num_essay=3):
    """
    지정된 수의 객관식 및 서술형 문제를 생성하는 함수
    """
    print(f"PDF 파일 '{pdf_path}'에서 문제 생성을 시작합니다...")
    
    # PDF 처리
    documents = process_pdf(pdf_path)
    
    if not documents:
        print("PDF 처리 실패. 문제를 생성할 수 없습니다.")
        return None
    
    # 문서 청킹 및 벡터 DB 생성
    chunks = chunk_documents(documents)
    vector_store = create_vector_db(chunks)
    retriever = get_retriever(vector_store)
    
    if not retriever:
        print("검색기 생성 실패. 문제를 생성할 수 없습니다.")
        return None
    
    # 문제 생성할 LLM 설정
    llm = ChatOpenAI(model="gpt-4o", temperature=0.7)
    
    # 객관식 문제 생성 프롬프트
    multiple_choice_prompt = PromptTemplate(
        template="""
        다음은 학습 자료의 내용입니다:
        {context}
        
        위 내용을 기반으로 객관식 문제 1개를 만들어주세요. 다음 형식을 정확히 따라주세요:
    
        # **문제**
        (질문 내용)
    
        ## 보기
        ① (선택지 1)  
        ② (선택지 2)  
        ③ (선택지 3)  
        ④ (선택지 4)  
    
        **정답:** (정답 번호)
        
        **해설:**  
        (문제 해설)
        
        **난이도:** (상/중/하)
        
        문제는 PDF 내용을 정확히 반영하고, 학습자의 이해도를 측정할 수 있도록 만들어주세요.
        
        중요 지침:
        1. 내용의 출처, 저자, 교수명, 페이지 번호 등에 관한 질문은 절대 하지 마세요.
        2. 저작권, 사용 조건, 배포 권한 등 문서의 형식적 측면에 관한 질문은 만들지 마세요.
        3. 반드시 문서의 실질적인 학습 내용, 개념, 이론에 관한 문제만 출제하세요.
        4. 이미지나 그래프가 있더라도 출처를 묻는 문제가 아닌, 그 내용을 이해했는지 평가하는 문제를 만드세요.
        5. 슬라이드 번호나 문서 구조보다는 내용 자체에 집중하세요.
        """,
        input_variables=["context"]
    )
    
    # 서술형 문제 생성 프롬프트
    essay_prompt = PromptTemplate(
        template="""
        다음은 학습 자료의 내용입니다:
        {context}
        
        위 내용을 기반으로 서술형 문제 1개를 만들어주세요. 다음 형식을 정확히 따라주세요:

        # **문제**
        (질문 내용)
        
        **모범 답안:**  
        (상세한 모범 답안)
        
        **채점 기준:**  
        (채점 시 중요 포인트 3-5개)
        
        **난이도:** (상/중/하)
        
        문제는 PDF 내용의 핵심 개념에 대한 깊은 이해를 평가할 수 있어야 합니다.
        
        중요 지침:
        1. 내용의 출처, 저자, 교수명, 페이지 번호 등에 관한 질문은 절대 하지 마세요.
        2. 저작권, 사용 조건, 배포 권한 등 문서의 형식적 측면에 관한 질문은 만들지 마세요.
        3. 반드시 문서의 실질적인 학습 내용, 개념, 이론에 관한 문제만 출제하세요.
        4. 이미지나 그래프가 있더라도 출처를 묻는 문제가 아닌, 그 내용을 이해했는지 평가하는 문제를 만드세요.
        5. 슬라이드 번호나 문서 구조보다는 내용 자체에 집중하세요.
        """,
        input_variables=["context"]
    )
    
    # 문제 생성
    multiple_choice_questions = []
    essay_questions = []
    
    # 객관식 문제 생성
    for i in range(num_multiple_choice):
        print(f"객관식 문제 {i+1}/{num_multiple_choice} 생성 중...")
        qa_chain = RetrievalQA.from_chain_type(
            llm, retriever=retriever, chain_type_kwargs={"prompt": multiple_choice_prompt}
        )
        result = qa_chain.invoke({"query": f"객관식 문제 {i+1}"})
        multiple_choice_questions.append(result["result"])
    
    # 서술형 문제 생성
    for i in range(num_essay):
        print(f"서술형 문제 {i+1}/{num_essay} 생성 중...")
        qa_chain = RetrievalQA.from_chain_type(
            llm, retriever=retriever, chain_type_kwargs={"prompt": essay_prompt}
        )
        result = qa_chain.invoke({"query": f"서술형 문제 {i+1}"})
        essay_questions.append(result["result"])
    
    print("문제 생성 완료!")
    
    return {
        "multiple_choice": multiple_choice_questions,
        "essay": essay_questions
    }

def save_questions_to_markdown(questions, output_path, pdf_title="문제집"):
    """
    생성된 문제를 마크다운 파일로 저장하는 함수
    """
    if not questions:
        print("저장할 문제가 없습니다.")
        return None
    
    try:
        with open(output_path, "w", encoding="utf-8") as file:
            # 문제집 제목 추가
            file.write(f"# {pdf_title}\n\n")
            
            # 객관식 문제 추가
            if questions["multiple_choice"]:
                file.write("## I. 객관식 문제\n\n")
                
                for i, q in enumerate(questions["multiple_choice"]):
                    # 문제 번호 추가 (원래 문제 형식은 유지)
                    q = q.replace("# **문제**", f"### 문제 {i+1}")
                    
                    # 마크다운 파일에 문제 추가
                    file.write(f"{q}\n\n")
                    
                    # 문제 사이에 구분선 추가
                    if i < len(questions["multiple_choice"]) - 1:
                        file.write("---\n\n")
            
            # 서술형 문제 추가
            if questions["essay"]:
                file.write("## II. 서술형 문제\n\n")
                
                for i, q in enumerate(questions["essay"]):
                    # 문제 번호 추가 (원래 문제 형식은 유지)
                    q = q.replace("# **문제**", f"### 문제 {i+1}")
                    
                    # 마크다운 파일에 문제 추가
                    file.write(f"{q}\n\n")
                    
                    # 문제 사이에 구분선 추가
                    if i < len(questions["essay"]) - 1:
                        file.write("---\n\n")
            
        print(f"문제집이 {output_path}에 마크다운 형식으로 저장되었습니다.")
        return output_path
    
    except Exception as e:
        print(f"마크다운 저장 중 오류 발생: {e}")
        return None
    
def convert_markdown_to_html(markdown_path):
    import markdown
    html_path = markdown_path.replace('.md', '.html')
    
    try:
        with open(markdown_path, 'r', encoding='utf-8') as f:
            md_content = f.read()
        
        html_content = markdown.markdown(md_content, extensions=['tables', 'fenced_code'])
        
        styled_html = f"""
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>문제집</title>
            <style>
                body {{ font-family: Arial, sans-serif; margin: 40px; }}
                h1 {{ color: #333; }}
                h2, h3 {{ color: #444; }}
                code {{ background-color: #f4f4f4; padding: 2px 5px; }}
                pre {{ background-color: #f4f4f4; padding: 10px; }}
                table {{ border-collapse: collapse; width: 100%; }}
                th, td {{ border: 1px solid #ddd; padding: 8px; }}
                th {{ background-color: #f0f0f0; }}
                @media print {{
                    body {{ margin: 1cm; }}
                    .page-break {{ page-break-before: always; }}
                }}
            </style>
        </head>
        <body>
            {html_content}
        </body>
        </html>
        """
        
        with open(html_path, 'w', encoding='utf-8') as f:
            f.write(styled_html)
        
        # 파일 경로를 절대 경로로 변환
        import os
        abs_path = os.path.abspath(html_path)
        file_url = f"file://{abs_path}"
        
        print(f"HTML 파일 생성 완료: {html_path}")
        print(f"브라우저에서 열고 Ctrl+P로 PDF로 인쇄할 수 있습니다: {file_url}")
        
        # 브라우저에서 열기
        import webbrowser
        webbrowser.open(file_url)
        
        return html_path
    except Exception as e:
        print(f"HTML 변환 중 오류 발생: {e}")
        return None


# 메인 실행 코드
def main():
    # 사용자 입력으로 PDF 경로 받기
    pdf_path = input("문제를 생성할 PDF 파일 경로를 입력하세요: ")
    
    if not os.path.exists(pdf_path):
        print(f"오류: '{pdf_path}' 파일이 존재하지 않습니다.")
        return
    
    # 파일명 추출하여 제목으로 사용
    pdf_filename = os.path.basename(pdf_path)
    pdf_title = os.path.splitext(pdf_filename)[0]
    
    # 객관식/서술형 문제 개수 입력
    try:
        num_multiple_choice = int(input("생성할 객관식 문제 개수 (기본값: 5): ") or 5)
        num_essay = int(input("생성할 서술형 문제 개수 (기본값: 3): ") or 3)
    except ValueError:
        print("숫자를 입력해야 합니다. 기본값으로 설정됩니다.")
        num_multiple_choice = 5
        num_essay = 3
    
    # PDF 파일 경로에서 확장자만 .md로 바꿔서 마크다운 파일 경로 생성
    markdown_output_path = os.path.splitext(pdf_path)[0] + "_문제집.md"
    
    # 문제 생성
    questions = generate_questions(pdf_path, num_multiple_choice, num_essay)
    
    if questions:
        title = f"{pdf_title} 문제집 (객관식 {num_multiple_choice}문제, 서술형 {num_essay}문제)"
        
        # 마크다운으로 저장
        markdown_output_path = os.path.splitext(pdf_path)[0] + "_문제집.md"
        md_file = save_questions_to_markdown(questions, markdown_output_path, pdf_title=title)
        
        print(f"마크다운 문제집 생성 완료: {md_file}")
        
        # HTML로 변환해서 브라우저에서 열기 (PDF 인쇄용)
        if md_file:
            html_file = convert_markdown_to_html(md_file)
            
# 직접 실행 시
if __name__ == "__main__":
    main()
