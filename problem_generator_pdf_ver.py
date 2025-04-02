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

def save_questions_to_pdf(questions, output_path, pdf_title="문제집"):
    """
    생성된 문제를 PDF로 저장하는 함수
    """
    if not questions:
        print("저장할 문제가 없습니다.")
        return None
        
    # 한글 폰트 등록 (한글 폰트가 필요한 경우)
    font_path = "C:/Windows/Fonts/malgun.ttf"
    if os.path.exists(font_path):
        pdfmetrics.registerFont(TTFont('MalgunGothic', font_path))
        font_name = 'MalgunGothic'
    else:
        font_name = 'Helvetica'
        print("맑은 고딕 폰트를 찾을 수 없어 기본 폰트를 사용합니다.")
    
    # PDF 문서 생성
    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        rightMargin=72,
        leftMargin=72,
        topMargin=72,
        bottomMargin=72
    )
    
    # 스타일 설정
    styles = getSampleStyleSheet()
    styles.add(ParagraphStyle(
        name='Korean',
        fontName=font_name,
        fontSize=10,
        leading=14,
    ))
    
    title_style = ParagraphStyle(
        'Title',
        parent=styles['Heading1'],
        fontName=font_name,
        fontSize=16,
        alignment=1,  # 가운데 정렬
        spaceAfter=20
    )
    
    subtitle_style = ParagraphStyle(
        'Subtitle',
        parent=styles['Heading2'],
        fontName=font_name,
        fontSize=14,
        spaceBefore=10,
        spaceAfter=10
    )
    heading_style = ParagraphStyle(
        'Heading',
        parent=styles['Heading2'],
        fontName=font_name,
        fontSize=12,
        spaceBefore=12,
        spaceAfter=6,
        textColor=colors.black
    )
    
    subheading_style = ParagraphStyle(
        'SubHeading',
        parent=styles['Heading3'],
        fontName=font_name,
        fontSize=11,
        spaceBefore=8,
        spaceAfter=4
    )
    
    question_style = ParagraphStyle(
        'Question',
        parent=styles['Korean'],
        fontName=font_name,
        fontSize=11,
        spaceBefore=6,
        spaceAfter=10,
        leading=16
    )
    
    # PDF에 들어갈 요소들
    elements = []
    
    # 제목 추가
    elements.append(Paragraph(pdf_title, title_style))
    elements.append(Spacer(1, 20))
    
    # 객관식 문제 추가
    if questions["multiple_choice"]:
        elements.append(Paragraph("I. 객관식 문제", subtitle_style))
        elements.append(Spacer(1, 10))
        
        for i, q in enumerate(questions["multiple_choice"]):
            # 마크다운과 유사한 형식을 처리하기 위한 전처리
            q = q.replace("# **문제**", f"<b>문제 {i+1}</b>")
            q = q.replace("## 보기", "<b>보기</b>")
            q = q.replace("**정답:**", "<b>정답:</b>")
            q = q.replace("**해설:**", "<b>해설:</b>")
            q = q.replace("**난이도:**", "<b>난이도:</b>")
            
            # 각 줄바꿈을 더 명확히 처리
            paragraphs = q.split("\n\n")
            for p in paragraphs:
                if p.strip():
                    if p.startswith("<b>문제"):
                        elements.append(Paragraph(p, heading_style))
                    elif p.startswith("<b>보기") or p.startswith("①") or p.startswith("②"):
                        elements.append(Paragraph(p, question_style))
                    elif p.startswith("<b>정답") or p.startswith("<b>해설") or p.startswith("<b>난이도"):
                        elements.append(Paragraph(p, subheading_style))
                    else:
                        elements.append(Paragraph(p, styles['Korean']))
            
            elements.append(Spacer(1, 20))
    
    # 서술형 문제 추가
    if questions["essay"]:
        elements.append(Paragraph("II. 서술형 문제", subtitle_style))
        elements.append(Spacer(1, 10))
        
        for i, q in enumerate(questions["essay"]):
            # 마크다운과 유사한 형식을 처리하기 위한 전처리
            q = q.replace("# **문제**", f"<b>문제 {i+1}</b>")
            q = q.replace("**모범 답안:**", "<b>모범 답안:</b>")
            q = q.replace("**채점 기준:**", "<b>채점 기준:</b>")
            q = q.replace("**난이도:**", "<b>난이도:</b>")
            
            # 각 줄바꿈을 더 명확히 처리
            paragraphs = q.split("\n\n")
            for p in paragraphs:
                if p.strip():
                    if p.startswith("<b>문제"):
                        elements.append(Paragraph(p, heading_style))
                    elif p.startswith("<b>모범 답안"):
                        elements.append(Paragraph(p, subheading_style))
                        # 모범 답안 내용이 다음 paragraph에 있을 것이므로 별도 처리는 필요 없음
                    elif p.startswith("<b>채점 기준") or p.startswith("<b>난이도"):
                        elements.append(Paragraph(p, subheading_style))
                    else:
                        elements.append(Paragraph(p, styles['Korean']))
            
            elements.append(Spacer(1, 20))
    
    # PDF 저장
    doc.build(elements)
    print(f"문제집이 {output_path}에 저장되었습니다.")
    return output_path

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
    
    # 출력 파일 경로 설정
    output_dir = os.path.dirname(pdf_path) or '.'
    output_path = os.path.join(output_dir, f"{pdf_title}_문제집.pdf")
    
    # 문제 생성
    questions = generate_questions(pdf_path, num_multiple_choice, num_essay)
    
    if questions:
        # PDF로 저장
        pdf_file = save_questions_to_pdf(
            questions, 
            output_path, 
            pdf_title=f"{pdf_title} 문제집 (객관식 {num_multiple_choice}문제, 서술형 {num_essay}문제)"
        )
        
        print(f"문제집 생성 완료: {pdf_file}")
    else:
        print("문제 생성에 실패했습니다.")

# 직접 실행 시
if __name__ == "__main__":
    main()
