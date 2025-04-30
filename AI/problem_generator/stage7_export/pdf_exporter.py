"""
@file        pdf_exporter.py
@author      MinJun Park
@date        2025-04-30
@description PDF 내보내기 모듈
             생성된 문제를 PDF 형식으로 변환하여 내보내는 기능을 제공하는 모듈

@update-log
"""

from typing import Dict
from reportlab.lib.pagesizes import letter
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Image
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
import os

# 문제 PDF/텍스트 저장 추상화 및 구현 모듈
# - 문제 데이터를 PDF 또는 텍스트 파일로 저장
# - 다양한 저장 방식(Reportlab, WeasyPrint 등)으로 확장 가능하게 설계


class IPdfExporter:
    """
    PDF/텍스트 저장 추상화 인터페이스 (SRP, OCP)
    - export: 문제 데이터를 PDF로 저장
    - 다양한 저장 방식(구현체)로 확장 가능
    - 입력: problem_data (dict), output_path (str)
    - 출력: 생성된 PDF 파일 경로(str)
    """

    def export(self, problem_data: Dict, output_path: str) -> str:
        raise NotImplementedError


class ReportlabPdfExporter(IPdfExporter):
    """
    Reportlab 기반 PDF 저장 구현체
    - 문제 데이터(dict)를 한글 폰트/스타일로 PDF로 저장
    - 그래프 이미지, 각 항목별 구분 등 지원
    - 입력: problem_data (dict), output_path (str)
    - 출력: 생성된 PDF 파일 경로(str)
    """

    def export(self, problem_data: Dict, output_path: str) -> str:
        # 1. 폰트 설정
        font_name = self._set_font()
        # 2. 스타일 설정
        styles = self._get_styles(font_name)
        # 3. PDF 요소 리스트 생성
        elements = []
        # 4. 제목 추가
        self._add_title(elements, problem_data, styles)
        # 5. 그래프 이미지 추가
        self._add_graph_image(elements, problem_data)
        # 6. 문제 항목(텍스트) 추가
        self._add_problem_items(elements, problem_data, styles)
        # 7. PDF 생성
        doc = SimpleDocTemplate(output_path, pagesize=letter)
        doc.build(elements)
        return output_path

    def _set_font(self) -> str:
        """
        한글 폰트가 있으면 등록하고 폰트 이름 반환, 없으면 기본 폰트 사용
        """
        font_path = "C:/Windows/Fonts/malgun.ttf"
        if os.path.exists(font_path):
            pdfmetrics.registerFont(TTFont("MalgunGothic", font_path))
            return "MalgunGothic"
        else:
            return "Helvetica"

    def _get_styles(self, font_name: str):
        """
        PDF 스타일 객체 생성 및 폰트 적용
        """
        styles = getSampleStyleSheet()
        for style_name in styles.byName:
            styles[style_name].fontName = font_name
        styles.add(
            ParagraphStyle(name="Korean", fontName=font_name, fontSize=10, leading=14)
        )
        return styles

    def _add_title(self, elements, problem_data, styles):
        """
        PDF 상단에 제목(과목명) 추가
        """
        subject = problem_data.get("subject", "수학")
        title = Paragraph(f"{subject} 문제", styles["Title"])
        elements.append(title)
        elements.append(Spacer(1, 12))

    def _add_graph_image(self, elements, problem_data):
        """
        그래프 이미지가 있으면 PDF에 추가
        """
        graph_img = problem_data.get("그래프_이미지")
        if graph_img and os.path.exists(graph_img):
            img = Image(graph_img, width=400, height=300)
            elements.append(img)
            elements.append(Spacer(1, 12))

    def _add_problem_items(self, elements, problem_data, styles):
        """
        문제 항목(텍스트)들을 PDF에 추가
        """
        for key, value in problem_data.items():
            if key in ["그래프", "subject", "그래프_이미지"] or not value:
                continue
            heading = Paragraph(f"{key}", styles["Heading2"])
            elements.append(heading)
            elements.append(Spacer(1, 6))
            text = str(value).replace("$", "")
            content = Paragraph(text, styles["Korean"])
            elements.append(content)
            elements.append(Spacer(1, 12))


def save_problem_as_text(problem_data: Dict, output_path: str) -> str:
    """
    문제 데이터를 텍스트 파일로 저장하는 함수
    - 입력: problem_data (dict), output_path (str)
    - 출력: 생성된 텍스트 파일 경로(str)
    - 각 항목별 구분, 그래프 함수/이미지 경로 등 포함
    """
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(f"{problem_data.get('subject', '수학')} 문제\n\n")
        for key, value in problem_data.items():
            if key not in ["그래프_이미지", "subject", "그래프"]:
                f.write(f"=== {key} ===\n")
                f.write(f"{value}\n\n")
        if "그래프" in problem_data and problem_data["그래프"]:
            f.write(f"=== 그래프 함수 ===\n")
            f.write(f"{problem_data['그래프']}\n\n")
        if "그래프_이미지" in problem_data and problem_data["그래프_이미지"]:
            f.write(f"=== 그래프 이미지 경로 ===\n")
            f.write(f"{problem_data['그래프_이미지']}\n\n")
    return output_path
