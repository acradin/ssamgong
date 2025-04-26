from typing import List, Any
from langchain_openai import ChatOpenAI
from langchain.prompts import PromptTemplate
from ..stage1_pdf_load.pdf_loader import load_pdf

# 과목 분류(텍스트 기반) 추상화 및 구현 모듈
# - PDF에서 추출한 텍스트를 바탕으로 국어/수학/영어/과학 등 과목을 자동 분류
# - LLM 기반, 프롬프트 외부화로 다양한 분류 전략 확장 가능


class ISubjectClassifier:
    """
    과목 분류 추상화 인터페이스 (SRP, OCP)
    - classify: PDF 파일 경로를 받아 과목명을 반환
    - 다양한 분류 전략(LLM, rule, etc) 확장 가능
    """

    def classify(self, pdf_path: str) -> str:
        raise NotImplementedError


class OpenAISubjectClassifier(ISubjectClassifier):
    """
    OpenAI LLM 기반 과목 분류 구현체
    - PDF에서 텍스트를 추출 후 LLM 프롬프트로 분류
    - 프롬프트는 외부 파일로 관리하여 유지보수 용이
    - 입력: pdf_path (str) - PDF 파일 경로
    - 출력: 과목명(국어/수학/영어/과학/기타)
    """

    def classify(self, pdf_path: str) -> str:
        documents = load_pdf(pdf_path)  # PDF에서 텍스트 추출
        full_text = " ".join([doc.page_content for doc in documents])
        llm = ChatOpenAI(model="gpt-4o", temperature=0)
        with open(
            "AI/pdf_search/prompts/subject_classification.txt", encoding="utf-8"
        ) as f:
            prompt_text = f.read()
        prompt = PromptTemplate(
            template=prompt_text,
            input_variables=["text"],
        )
        chain = prompt | llm
        result = chain.invoke({"text": full_text[:4000]})  # LLM 입력 길이 제한 고려
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
