import os
from typing import Any, Dict, List, Optional
from langchain_openai import ChatOpenAI
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
from langchain.output_parsers import PydanticOutputParser
from pydantic import BaseModel, Field
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

    def generate(self, retriever: Any, variables: Dict[str, Any] = None) -> str:
        raise NotImplementedError


class Problem(BaseModel):
    passage: Optional[str] = Field(None, description="지문 내용 (있는 경우)")
    situation: Optional[str] = Field(None, description="상황 설명 (있는 경우)")
    question: str = Field(description="문제 내용")
    options: Optional[List[str]] = Field(None, description="선택지 (객관식인 경우)")
    answer: str = Field(description="정답")
    explanation: str = Field(description="해설")
    difficulty: str = Field(description="난이도 (상/중/하)")
    graph: Optional[str] = Field(None, description="그래프 함수식 (있는 경우)")

    def format_output(self, subject: str) -> str:
        """과목별 형식에 맞게 출력 문자열 생성"""
        if subject == "수학":
            return self._format_math()
        elif subject == "국어":
            return self._format_korean()
        elif subject == "영어":
            return self._format_english()
        elif subject == "과학":
            return self._format_science()
        return self._format_default()

    def _format_math(self) -> str:
        output = []
        output.append("[문제]")
        output.append(self.question)
        if self.options:
            output.append("[보기]")
            for i, opt in enumerate(self.options, 1):
                output.append(f"① {opt}")
        output.append("[정답]")
        output.append(self.answer)
        output.append("[해설]")
        output.append(self.explanation)
        output.append("[난이도]")
        output.append(self.difficulty)
        if self.graph:
            output.append("[그래프]")
            output.append(self.graph)
        return "\n".join(output)

    def _format_korean(self) -> str:
        output = []
        if self.passage:
            output.append("[지문]")
            output.append(self.passage)
        output.append("[문제]")
        output.append(self.question)
        if self.options:
            output.append("[보기]")
            for i, opt in enumerate(self.options, 1):
                output.append(f"① {opt}")
        output.append("[정답]")
        output.append(self.answer)
        output.append("[해설]")
        output.append(self.explanation)
        output.append("[난이도]")
        output.append(self.difficulty)
        return "\n".join(output)

    def _format_english(self) -> str:
        output = []
        if self.passage:
            output.append("[Passage]")
            output.append(self.passage)
        output.append("[Question]")
        output.append(self.question)
        if self.options:
            output.append("[Options]")
            for i, opt in enumerate(self.options, 1):
                output.append(f"① {opt}")
        output.append("[Answer]")
        output.append(self.answer)
        output.append("[Explanation]")
        output.append(self.explanation)
        output.append("[Difficulty]")
        output.append(self.difficulty)
        return "\n".join(output)

    def _format_science(self) -> str:
        output = []
        if self.situation:
            output.append("[상황 설명]")
            output.append(self.situation)
        output.append("[문제]")
        output.append(self.question)
        if self.options:
            output.append("[보기]")
            for i, opt in enumerate(self.options, 1):
                output.append(f"① {opt}")
        output.append("[정답]")
        output.append(self.answer)
        output.append("[해설]")
        output.append(self.explanation)
        if self.graph:
            output.append("[그래프 설명]")
            output.append(self.graph)
        output.append("[난이도]")
        output.append(self.difficulty)
        return "\n".join(output)

    def _format_default(self) -> str:
        return self.dict(exclude_none=True)


class Problems(BaseModel):
    problems: List[Problem] = Field(description="생성된 문제들의 리스트")


class BaseProblemGenerator(IProblemGenerator):
    def __init__(self):
        self.llm = ChatOpenAI(model="gpt-4", temperature=0.7)
        self.base_dir = os.path.dirname(
            os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        )
        self.prompts_dir = os.path.join(self.base_dir, "problem_generator", "prompts")

    def get_prompt_template(self) -> str:
        """각 과목별 프롬프트 템플릿 파일명을 반환"""
        raise NotImplementedError

    def get_subject(self) -> str:
        """과목명 반환"""
        return self.__class__.__name__.replace("ProblemGenerator", "")

    def _parse_problem(self, text: str) -> Problem:
        """텍스트 형식의 문제를 Problem 객체로 파싱"""
        lines = text.strip().split("\n")
        problem_data = {}
        current_section = None
        current_content = []

        for line in lines:
            line = line.strip()
            if not line:
                continue

            if line.startswith("[") and line.endswith("]"):
                if current_section and current_content:
                    key = self._get_field_name(current_section)
                    if key:
                        problem_data[key] = "\n".join(current_content).strip()
                current_section = line[1:-1]
                current_content = []
            else:
                current_content.append(line)

        if current_section and current_content:
            key = self._get_field_name(current_section)
            if key:
                problem_data[key] = "\n".join(current_content).strip()

        # 선택지 파싱
        if "보기" in problem_data:
            options = []
            for line in problem_data["보기"].split("\n"):
                if (
                    line.startswith("①")
                    or line.startswith("②")
                    or line.startswith("③")
                    or line.startswith("④")
                ):
                    options.append(line[2:].strip())
            problem_data["options"] = options
            del problem_data["보기"]

        return Problem(**problem_data)

    def _get_field_name(self, section: str) -> Optional[str]:
        """섹션 이름을 Problem 클래스의 필드명으로 변환"""
        mapping = {
            "문제": "question",
            "Question": "question",
            "정답": "answer",
            "Answer": "answer",
            "해설": "explanation",
            "Explanation": "explanation",
            "난이도": "difficulty",
            "Difficulty": "difficulty",
            "지문": "passage",
            "Passage": "passage",
            "상황 설명": "situation",
            "그래프": "graph",
            "그래프 설명": "graph",
            "보기": "보기",
            "Options": "보기",
        }
        return mapping.get(section)

    def generate(self, retriever: Any, variables: Dict[str, Any] = None) -> str:
        if variables is None:
            variables = {}

        # 관련 문서 검색
        relevant_docs = retriever.get_relevant_documents(
            variables.get("additional_prompt") or f"{self.get_subject()} 문제"
        )
        variables["context"] = "\n".join([doc.page_content for doc in relevant_docs])

        # 프롬프트 템플릿 로드
        with open(
            os.path.join(self.prompts_dir, self.get_prompt_template()), encoding="utf-8"
        ) as f:
            prompt_template = f.read()

        # 프롬프트 생성
        prompt = PromptTemplate(
            template=prompt_template,
            input_variables=[
                "context",
                "school_level",
                "grade",
                "exam_type",
                "num_problems",
                "difficulty",
                "problem_type",
                "additional_prompt",
            ],
        )

        # LLM 호출 및 결과 원본 반환
        result = self.llm.invoke(prompt.format(**variables))
        return result.content


class MathProblemGenerator(BaseProblemGenerator):
    """
    수학 문제 생성기 구현체
    - 수학 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도/그래프 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def get_prompt_template(self) -> str:
        return "math_problem.txt"


class KoreanProblemGenerator(BaseProblemGenerator):
    """
    국어 문제 생성기 구현체
    - 국어 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def get_prompt_template(self) -> str:
        return "korean_problem.txt"


class EnglishProblemGenerator(BaseProblemGenerator):
    """
    영어 문제 생성기 구현체
    - 영어 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def get_prompt_template(self) -> str:
        return "english_problem.txt"


class ScienceProblemGenerator(BaseProblemGenerator):
    """
    과학 문제 생성기 구현체
    - 과학 관련 문서에서 LLM 프롬프트로 문제/정답/해설/난이도/그래프 등 생성
    - 프롬프트는 외부 파일로 관리
    - 입력: retriever (문서 검색기), prompt (사용자 요청 텍스트)
    - 출력: 문제 데이터(dict)
    """

    def get_prompt_template(self) -> str:
        return "science_problem.txt"


class EtcProblemGenerator(BaseProblemGenerator):
    """
    기타(기본) 문제 생성기 구현체
    - 별도의 과목 분류가 없는 경우 사용
    - 프롬프트는 외부 파일로 관리 (없으면 간단한 기본 템플릿 사용)
    """

    def get_prompt_template(self) -> str:
        return "etc_problem.txt"
