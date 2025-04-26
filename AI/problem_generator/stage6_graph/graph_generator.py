from typing import Optional
import numpy as np
import matplotlib.pyplot as plt
import os
import re

# 그래프 생성기 추상화 및 구현 모듈
# - 수학/과학 문제에서 함수식 또는 설명을 받아 그래프 이미지를 생성
# - 다양한 그래프 엔진(matplotlib 등)으로 확장 가능하게 설계


class IGraphGenerator:
    """
    그래프 생성기 추상화 인터페이스 (SRP, OCP)
    - generate: 함수식(또는 설명) 문자열을 받아 그래프 이미지 파일 경로 반환
    - 다양한 그래프 생성 방식(엔진)으로 확장 가능
    - 입력: function_text (str) - 함수식 또는 그래프 설명
    - 출력: 이미지 파일 경로(str) 또는 None
    """

    def generate(self, function_text: str) -> Optional[str]:
        raise NotImplementedError


class MatplotlibGraphGenerator(IGraphGenerator):
    """
    matplotlib 기반 그래프 생성기 구현체
    - 수학 함수식(예: y = x^2, -5 <= x <= 5) 또는 과학 그래프 설명을 파싱하여 이미지 생성
    - 입력: function_text (str)
    - 출력: 생성된 그래프 이미지 파일 경로(str) 또는 None
    - 내부 동작:
        1. 정규표현식으로 함수식/범위 추출
        2. numpy로 x/y 데이터 생성
        3. matplotlib로 그래프 그려 파일로 저장
    """

    def generate(self, function_text: str) -> Optional[str]:
        function_match = re.search(r"y\s*=\s*(.+?)(?:,|$)", function_text)
        range_match = re.search(
            r"(-?\d+\.?\d*)\s*<=\s*x\s*<=\s*(-?\d+\.?\d*)", function_text
        )
        if not function_match:
            return None
        function_expr = function_match.group(1).strip().replace("^", "**")
        x_min, x_max = -10, 10
        if range_match:
            x_min = float(range_match.group(1))
            x_max = float(range_match.group(2))
        x = np.linspace(x_min, x_max, 1000)
        y = eval(
            function_expr,
            {"__builtins__": {}},
            {
                "x": x,
                "np": np,
                "sin": np.sin,
                "cos": np.cos,
                "tan": np.tan,
                "sqrt": np.sqrt,
            },
        )
        plt.figure(figsize=(8, 6))
        plt.plot(x, y)
        plt.grid(True)
        plt.axhline(y=0, color="k", linestyle="-", alpha=0.3)
        plt.axvline(x=0, color="k", linestyle="-", alpha=0.3)
        plt.title(f'$y = {function_expr.replace("**", "^")}$')
        plt.xlabel("x")
        plt.ylabel("y")
        save_path = os.path.join(os.getcwd(), "temp_graph.png")
        plt.savefig(save_path)
        plt.close()
        return save_path


def generate_science_graph(description: str) -> Optional[str]:
    """
    과학 문제용 그래프 생성 헬퍼 함수
    - description: 함수식 또는 그래프 설명
    - 반환: 생성된 그래프 이미지 파일 경로(str) 또는 None
    """
    return MatplotlibGraphGenerator().generate(description)
