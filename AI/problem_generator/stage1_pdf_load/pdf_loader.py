import base64
from langchain_community.document_loaders import PyPDFLoader
from langchain_core.documents import Document
from langchain_openai import ChatOpenAI

# PDF 파일을 다양한 방법으로 로드하는 유틸리티 모듈
# - 1순위: PyPDFLoader (langchain)
# - 2순위: PyMuPDF (fitz, 한글 PDF 등 강력)
# - 3순위: GPT-4 Vision (OCR 기반, 최후의 수단)


def load_with_pypdfloader(pdf_path):
    """
    PyPDFLoader를 사용하여 PDF 파일을 로드합니다.
    - 입력: pdf_path (str) : PDF 파일 경로
    - 출력: Document 객체 리스트 (페이지별)
    - 실패 시 None 반환
    """
    try:
        loader = PyPDFLoader(pdf_path)
        pages = loader.load()
        print(f"PyPDFLoader로 성공적으로 로드했습니다. 페이지 수: {len(pages)}")
        return pages
    except Exception as e:
        print(f"PyPDFLoader 오류: {e}")
        return None


def load_with_pymupdf(pdf_path):
    """
    PyMuPDF(fitz)를 사용하여 PDF 파일을 로드합니다.
    - 입력: pdf_path (str) : PDF 파일 경로
    - 출력: Document 객체 리스트 (페이지별)
    - fitz는 한글 PDF 등에서 강력한 텍스트 추출 성능을 보임
    - 실패 시 None 반환
    """
    try:
        import fitz

        doc = fitz.open(pdf_path)
        pages = []
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            content = page.get_text()
            pages.append(
                Document(
                    page_content=content,
                    metadata={"page": page_num, "source": pdf_path},
                )
            )
        print(f"PyMuPDF로 성공적으로 로드했습니다. 페이지 수: {len(pages)}")
        return pages
    except Exception as e:
        print(f"PyMuPDF 오류: {e}")
        return None


def extract_text_from_pdf_with_vision(pdf_path):
    """
    GPT-4 Vision API를 사용하여 PDF에서 텍스트를 추출합니다.
    - 입력: pdf_path (str) : PDF 파일 경로
    - 출력: 추출된 전체 텍스트 (str)
    - 이미지 기반 PDF, 난독화 PDF 등에서 최후의 수단으로 사용
    """
    with open(pdf_path, "rb") as file:
        pdf_bytes = file.read()
    base64_pdf = base64.b64encode(pdf_bytes).decode("utf-8")
    vision_model = ChatOpenAI(model="gpt-4o", temperature=0)
    messages = [
        {
            "role": "user",
            "content": [
                {
                    "type": "text",
                    "text": "이 PDF의 모든 텍스트 내용을 추출해주세요. 원본 텍스트 형식을 최대한 유지하고, 페이지 구분 없이 전체 내용을 추출해주세요.",
                },
                {
                    "type": "image_url",
                    "image_url": {"url": f"data:application/pdf;base64,{base64_pdf}"},
                },
            ],
        }
    ]
    response = vision_model.invoke(messages)
    return response.content


def load_pdf(pdf_path):
    """
    PDF 파일을 다양한 방법(PyPDFLoader, PyMuPDF, Vision)으로 로드합니다.
    - 입력: pdf_path (str) : PDF 파일 경로
    - 출력: Document 객체 리스트 (페이지별 또는 전체)
    - 내부적으로 1) PyPDFLoader, 2) PyMuPDF, 3) Vision 순서로 시도
    - 모든 방법 실패 시 빈 리스트 반환
    """
    print(f"PDF 로드 시도: {pdf_path}")
    # 1. PyPDFLoader 시도
    pages = load_with_pypdfloader(pdf_path)
    if pages:
        return pages
    # 2. PyMuPDF 시도
    pages = load_with_pymupdf(pdf_path)
    if pages:
        return pages
    # 3. Vision 등 기타 방식 (최후의 수단)
    print("GPT-4 Vision으로 텍스트 추출 시도...")
    extracted_text = extract_text_from_pdf_with_vision(pdf_path)
    if extracted_text:
        print(f"추출된 텍스트 일부: {extracted_text[:100]}...")
        return [Document(page_content=extracted_text, metadata={"source": pdf_path})]
    print("모든 PDF 로드 방법이 실패했습니다.")
    return []
