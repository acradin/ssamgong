from openai import OpenAI
from PyPDF2 import PdfReader
from typing import List, Dict
import os
from dotenv import load_dotenv
from pdf2image import convert_from_path
import pytesseract
from PIL import Image
import tempfile
import base64


class PDFAnalyzer:
    def __init__(self, pdf_path: str):
        """
        PDF 분석기 초기화

        Args:
            pdf_path (str): PDF 파일 경로
        """
        load_dotenv()
        self.client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))
        self.pdf_path = pdf_path
        self.reader = PdfReader(pdf_path)

    def get_pdf_base64(self) -> str:
        """
        PDF 파일을 base64로 인코딩

        Returns:
            str: base64로 인코딩된 PDF 파일
        """
        with open(self.pdf_path, "rb") as pdf_file:
            return base64.b64encode(pdf_file.read()).decode("utf-8")

    def is_scanned_pdf(self) -> bool:
        """
        PDF가 스캔본인지 확인

        Returns:
            bool: 스캔본 여부
        """
        # 첫 페이지에서 텍스트 추출 시도
        first_page = self.reader.pages[0]
        text = first_page.extract_text()

        # 텍스트가 거의 없거나 비어있으면 스캔본으로 간주
        return len(text.strip()) < 50

    def extract_text_from_image(self, image: Image.Image) -> str:
        """
        이미지에서 텍스트 추출 (OCR)

        Args:
            image (Image.Image): PIL Image 객체

        Returns:
            str: 추출된 텍스트
        """
        # Tesseract OCR 설정
        custom_config = r"--oem 3 --psm 6 -l kor+eng"
        return pytesseract.image_to_string(image, config=custom_config)

    def extract_text_from_pdf(self) -> List[Dict[str, str]]:
        """
        일반 PDF에서 텍스트 추출

        Returns:
            List[Dict[str, str]]: 페이지 번호와 텍스트를 포함하는 딕셔너리 리스트
        """
        pages = []
        for page_num in range(len(self.reader.pages)):
            page = self.reader.pages[page_num]
            text = page.extract_text()
            pages.append({"page_number": page_num + 1, "text": text})
        return pages

    def extract_text_from_scanned_pdf(self) -> List[Dict[str, str]]:
        """
        스캔본 PDF에서 텍스트 추출 (OCR 사용)

        Returns:
            List[Dict[str, str]]: 페이지 번호와 텍스트를 포함하는 딕셔너리 리스트
        """
        pages = []
        with tempfile.TemporaryDirectory() as temp_dir:
            images = convert_from_path(self.pdf_path, output_folder=temp_dir)
            for page_num, image in enumerate(images):
                text = self.extract_text_from_image(image)
                pages.append({"page_number": page_num + 1, "text": text})
                image.close()
        return pages

    def extract_text(self) -> List[Dict[str, str]]:
        """
        PDF에서 텍스트 추출 (PDF 타입에 따라 적절한 방법 선택)

        Returns:
            List[Dict[str, str]]: 페이지 번호와 텍스트를 포함하는 딕셔너리 리스트
        """
        if self.is_scanned_pdf():
            print("스캔본 PDF 감지: OCR을 사용하여 텍스트 추출")
            return self.extract_text_from_scanned_pdf()
        else:
            print("일반 PDF 감지: 직접 텍스트 추출")
            return self.extract_text_from_pdf()

    def analyze_content(self, query: str) -> str:
        """
        PDF 내용을 GPT에게 전달하여 분석 요청

        Args:
            query (str): 분석 요청 쿼리

        Returns:
            str: GPT의 분석 결과
        """
        # PDF 텍스트 추출
        print("PDF에서 텍스트 추출 중...")
        pages = self.extract_text()
        print("텍스트 추출 완료")

        # 전체 텍스트를 하나의 문자열로 결합
        full_text = "\n\n".join(
            [f"페이지 {page['page_number']}:\n{page['text']}" for page in pages]
        )

        # PDF 파일을 base64로 인코딩
        pdf_base64 = self.get_pdf_base64()

        # GPT에게 전달할 프롬프트 구성
        prompt = f"""다음은 PDF 문서의 내용입니다. 이 내용을 바탕으로 다음 질문에 답변해주세요:

문서 내용:
{full_text}

질문: {query}

답변은 한국어로 작성해주세요."""

        # GPT API 호출
        print("GPT 분석 중...")
        file = self.client.files.create(
            file=open(self.pdf_path, "rb"),
            purpose="user_data",
        )

        response = self.client.response.create(
            model="gpt-4o",
            messages=[
                {
                    "role": "system",
                    "content": "당신은 PDF 문서의 내용을 분석하고 질문에 답변하는 전문가입니다.",
                },
                {
                    "role": "user",
                    "content": [
                        {"type": "text", "text": prompt},
                        {
                            "type": "input_file",
                            "file_data": f"data:application/pdf;base64,{pdf_base64}",
                                
                        },
                        {
                            "type" : "input_file",
                            "file_id" : file.id,
                        },
                    ],
                },
            ],
            temperature=0.7,
            max_tokens=2000,
        )

        return response.choices[0].message.content

    @staticmethod
    def is_valid_pdf(file_path: str) -> bool:
        """
        파일이 유효한 PDF인지 확인

        Args:
            file_path (str): 파일 경로

        Returns:
            bool: PDF 파일 여부
        """
        return file_path.lower().endswith(".pdf") and os.path.exists(file_path)
