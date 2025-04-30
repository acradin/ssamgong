import os
from typing import Any, Dict, List
from langchain_openai import ChatOpenAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
import re
import json
import ast

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

    def generate(self, retriever: Any, prompt: str = "") -> List[dict]:
        raise NotImplementedError


class BaseProblemGenerator(IProblemGenerator):
    def __init__(self):
        self.llm = ChatOpenAI(model="gpt-4", temperature=0.7)
        self.base_dir = os.path.dirname(
            os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        )
        self.prompts_dir = os.path.join(self.base_dir, "problem_generator", "prompts")

    def parse_result(self, result_content: str) -> List[dict]:
        """안전하게 LLM 결과를 파싱하는 메서드"""
        try:
            # 먼저 ast.literal_eval로 시도
            return ast.literal_eval(result_content)
        except (ValueError, SyntaxError):
            try:
                # JSON 형식으로 시도
                return json.loads(result_content)
            except json.JSONDecodeError:
                # 파이프로 구분된 텍스트를 파싱
                problems = []
                lines = result_content.strip().split("\n")
                for line in lines:
                    parts = [p.strip() for p in line.split("|")]
                    if len(parts) >= 4:  # 최소 4개의 필드가 있는지 확인
                        problem = {
                            "type": parts[0],
                            "difficulty": parts[1],
                            "question": parts[2],
                            "answer": parts[3],
                            "explanation": parts[4] if len(parts) > 4 else "",
                        }
                        problems.append(problem)
                return problems

    def generate(self, retriever: Any, prompt: str = "") -> List[dict]:
        raise NotImplementedError


class MathProblemGenerator(BaseProblemGenerator):
    """
    수학 문제 생성기 구현체
    - 수학 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도/그래프 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any, prompt: str = "") -> List[dict]:
        relevant_docs = retriever.get_relevant_documents(prompt or "수학 문제")
        context = "\n".join([doc.page_content for doc in relevant_docs])

        with open(
            os.path.join(self.prompts_dir, "math_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_template = f.read()

        prompt = PromptTemplate(
            template=prompt_template,
            input_variables=["context", "prompt"],
        )
        chain = prompt | self.llm
        result = chain.invoke({"context": context, "prompt": prompt})
        return self.parse_result(result.content)


class KoreanProblemGenerator(BaseProblemGenerator):
    """
    국어 문제 생성기 구현체
    - 국어 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any, prompt: str = "") -> List[dict]:
        relevant_docs = retriever.get_relevant_documents(prompt or "국어 문제")
        context = "\n".join([doc.page_content for doc in relevant_docs])

        with open(
            os.path.join(self.prompts_dir, "korean_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_template = f.read()

        prompt = PromptTemplate(
            template=prompt_template,
            input_variables=["context", "prompt"],
        )
        chain = prompt | self.llm
        result = chain.invoke({"context": context, "prompt": prompt})
        return self.parse_result(result.content)


class EnglishProblemGenerator(BaseProblemGenerator):
    """
    영어 문제 생성기 구현체
    - 영어 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any, prompt: str = "") -> List[dict]:
        relevant_docs = retriever.get_relevant_documents(prompt or "영어 문제")
        context = "\n".join([doc.page_content for doc in relevant_docs])

        with open(
            os.path.join(self.prompts_dir, "english_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_template = f.read()

        prompt = PromptTemplate(
            template=prompt_template,
            input_variables=["context", "prompt"],
        )
        chain = prompt | self.llm
        result = chain.invoke({"context": context, "prompt": prompt})
        return self.parse_result(result.content)


class ScienceProblemGenerator(BaseProblemGenerator):
    """
    과학 문제 생성기 구현체
    - 과학 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도/그래프 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def generate(self, retriever: Any, prompt: str = "") -> List[dict]:
        relevant_docs = retriever.get_relevant_documents(prompt or "과학 문제")
        context = "\n".join([doc.page_content for doc in relevant_docs])

        with open(
            os.path.join(self.prompts_dir, "science_problem.txt"), encoding="utf-8"
        ) as f:
            prompt_template = f.read()

        prompt = PromptTemplate(
            template=prompt_template,
            input_variables=["context", "prompt"],
        )
        chain = prompt | self.llm
        result = chain.invoke({"context": context, "prompt": prompt})
        return self.parse_result(result.content)
