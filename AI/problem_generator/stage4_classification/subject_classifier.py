from typing import List, Any
from langchain_openai import ChatOpenAI
from langchain.prompts import PromptTemplate
from ..stage1_pdf_load.pdf_loader import load_pdf
from ..stage5_problem_generation.problem_generator import (
    BaseProblemGenerator,
    MathProblemGenerator,
    KoreanProblemGenerator,
    EnglishProblemGenerator,
    ScienceProblemGenerator,
)
import os
import base64

# 과목 분류(텍스트 기반) 추상화 및 구현 모듈
# - PDF에서 추출한 텍스트를 바탕으로 국어/수학/영어/과학 등 과목을 자동 분류
# - LLM 기반, 프롬프트 외부화로 다양한 분류 전략 확장 가능


class ISubjectClassifier:
    """
    과목 분류 추상화 인터페이스 (SRP, OCP)
    - classify: PDF 파일 경로를 받아 과목명을 반환
    - 다양한 분류 전략(LLM, rule, etc) 확장 가능
    """

    def classify(self, pdf_path: str, loaded_documents: List = None) -> str:
        raise NotImplementedError

    def get_problem_generator(self, subject: str) -> BaseProblemGenerator:
        raise NotImplementedError


class OpenAISubjectClassifier(ISubjectClassifier):
    """
    OpenAI LLM 기반 과목 분류 구현체
    - PDF에서 텍스트를 추출 후 LLM 프롬프트로 분류
    - 프롬프트는 외부 파일로 관리하여 유지보수 용이
    - 입력: pdf_path (str) - PDF 파일 경로
    - 출력: 과목명(국어/수학/영어/과학/기타)
    """

    def __init__(self):
        self.llm = ChatOpenAI(model="gpt-4o-mini", temperature=0)
        self.base_dir = os.path.dirname(
            os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        )
        self.prompts_dir = os.path.join(self.base_dir, "problem_generator", "prompts")

    def get_problem_generator(self, subject: str) -> BaseProblemGenerator:
        """
        과목에 따른 문제 생성기를 반환하는 함수

        Args:
            subject (str): 분류된 과목명

        Returns:
            BaseProblemGenerator: 해당 과목의 문제 생성기 인스턴스

        Raises:
            ValueError: 지원하지 않는 과목인 경우
        """
        generators = {
            "수학": MathProblemGenerator,
            "국어": KoreanProblemGenerator,
            "영어": EnglishProblemGenerator,
            "과학": ScienceProblemGenerator,
        }

        if subject not in generators:
            raise ValueError("지원하지 않는 과목입니다.")

        return generators[subject]()

    def get_pdf_base64(self, pdf_path: str) -> str:
        """PDF 파일을 base64로 인코딩"""
        with open(pdf_path, "rb") as file:
            return base64.b64encode(file.read()).decode("utf-8")

    def classify(self, pdf_path: str, loaded_documents: List = None) -> str:
        """
        PDF 파일의 과목을 분류하는 메서드

        Args:
            pdf_path (str): PDF 파일 경로
            loaded_documents (List, optional): 이미 로드된 PDF 문서 리스트

        Returns:
            str: 분류된 과목명
        """
        # 문서가 전달되지 않은 경우에만 로드
        if loaded_documents is None:
            documents = load_pdf(pdf_path)
        else:
            documents = loaded_documents

        full_text = " ".join([doc.page_content for doc in documents])

        # PDF 파일을 base64로 인코딩
        pdf_base64 = self.get_pdf_base64(pdf_path)

        with open(
            os.path.join(self.prompts_dir, "subject_classification.txt"),
            encoding="utf-8",
        ) as f:
            prompt_template = f.read()

        prompt = PromptTemplate(
            template=prompt_template,
            input_variables=["text", "pdf_base64"],
        )

        chain = prompt | self.llm

        # 텍스트와 PDF 파일 모두 전송
        result = chain.invoke(
            {
                "text": full_text[:4000],  # LLM 입력 길이 제한 고려
                "pdf_base64": pdf_base64,
            }
        )

        subject = result.content.strip()

        # LLM 응답에서 과목명만 추출(정규화)
        if "국어" in subject:
            return "국어"
        elif "수학" in subject:
            return "수학"
        elif "영어" in subject:
            return "영어"
        elif "과학" in subject:
            return "과학"
        else:
            return "기타"
