import os
from typing import Any, Dict
from langchain_openai import ChatOpenAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
import re

# 문제 생성기 추상화 및 구현 모듈
# - 벡터스토어 기반 문서 질의 결과를 바탕으로 LLM이 과목별 문제를 생성
# - 프롬프트 외부화로 다양한 문제 유형/포맷 확장 가능
# - SRP/OCP/ISP 원칙 적용


class IProblemGenerator:
    """
    문제 생성기 추상화 인터페이스 (SRP, OCP, ISP)
    - generate: 벡터스토어 retriever를 받아 문제 데이터를 생성
    - 과목별/유형별 다양한 문제 생성기로 확장 가능
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any, text: str) -> Dict:
        raise NotImplementedError


class MathProblemGenerator(IProblemGenerator):
    """
    수학 문제 생성기 구현체
    - 수학 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도/그래프 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), text (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any, text: str) -> Dict:
        llm = ChatOpenAI(model="gpt-4o", temperature=0.7)
        with open(
            os.path.join("AI/pdf_search/prompts/math_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_text = f.read()
        prompt = PromptTemplate(
            template=prompt_text, input_variables=["context", "user_text"]
        )
        chain = RetrievalQA.from_chain_type(
            llm=llm,
            chain_type="stuff",
            retriever=retriever,
            chain_type_kwargs={"prompt": prompt},
            return_source_documents=True,
        )
        result = chain.invoke({"query": "수학 문제 생성"})
        return result


class KoreanProblemGenerator(IProblemGenerator):
    """
    국어 문제 생성기 구현체
    - 국어 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any) -> Dict:
        llm = ChatOpenAI(model="gpt-4o", temperature=0.7)
        with open(
            os.path.join("AI/pdf_search/prompts/korean_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_text = f.read()
        QA_CHAIN_PROMPT = PromptTemplate(
            template=prompt_text, input_variables=["context"]
        )
        qa_chain = RetrievalQA.from_chain_type(
            llm,
            retriever=retriever,
            return_source_documents=True,
            chain_type_kwargs={"prompt": QA_CHAIN_PROMPT},
        )
        result = qa_chain.invoke({"query": "국어 문제 생성"})
        return {"result": result["result"]}


class EnglishProblemGenerator(IProblemGenerator):
    """
    영어 문제 생성기 구현체
    - 영어 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any) -> Dict:
        llm = ChatOpenAI(model="gpt-4o", temperature=0.7)
        with open(
            os.path.join("AI/pdf_search/prompts/english_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_text = f.read()
        QA_CHAIN_PROMPT = PromptTemplate(
            template=prompt_text, input_variables=["context"]
        )
        qa_chain = RetrievalQA.from_chain_type(
            llm,
            retriever=retriever,
            return_source_documents=True,
            chain_type_kwargs={"prompt": QA_CHAIN_PROMPT},
        )
        result = qa_chain.invoke({"query": "영어 문제 생성"})
        return {"result": result["result"]}


class ScienceProblemGenerator(IProblemGenerator):
    """
    과학 문제 생성기 구현체
    - 과학 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도/그래프 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any) -> Dict:
        llm = ChatOpenAI(model="gpt-4o", temperature=0.7)
        with open(
            os.path.join("AI/pdf_search/prompts/science_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_text = f.read()
        QA_CHAIN_PROMPT = PromptTemplate(
            template=prompt_text, input_variables=["context"]
        )
        qa_chain = RetrievalQA.from_chain_type(
            llm,
            retriever=retriever,
            return_source_documents=True,
            chain_type_kwargs={"prompt": QA_CHAIN_PROMPT},
        )
        result = qa_chain.invoke({"query": "과학 문제 생성"})
        return {"result": result["result"]}
