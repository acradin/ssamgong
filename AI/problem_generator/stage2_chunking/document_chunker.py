"""
@file        document_chunker.py
@author      MinJun Park
@date        2025-04-30
@description 문서 청킹(분할) 모듈
             긴 문서를 LLM이 처리하기 좋은 크기로 분할하는 기능을 제공하는 모듈

@update-log
"""

from typing import List, Any
from langchain.text_splitter import RecursiveCharacterTextSplitter

# 문서 청킹(분할) 추상화 및 구현 모듈
# - 긴 문서를 LLM이 처리하기 좋은 크기로 쪼개는 역할
# - 다양한 청킹 전략을 확장 가능하게 설계


class IDocumentChunker:
    """
    문서 청킹(분할) 인터페이스 (SRP, OCP)
    - 하나의 문서 리스트를 여러 청크(조각)로 나누는 역할
    - chunk 메서드만 정의 (구현체에서 실제 분할 방식 결정)
    """

    def chunk(self, documents: List[Any]) -> List[Any]:
        raise NotImplementedError


class RecursiveChunker(IDocumentChunker):
    """
    langchain의 RecursiveCharacterTextSplitter를 활용한 기본 청킹 구현체
    - chunk_size: 각 청크의 최대 길이(문자 수)
    - chunk_overlap: 청크 간 중복 허용 길이(문자 수)
    - separators: 분할 우선순위(문단, 줄바꿈, 공백 등)
    - 입력: documents (List[Any]) - langchain Document 객체 리스트 등
    - 출력: 청크 리스트(List[Any])
    """

    def chunk(self, documents: List[Any]) -> List[Any]:
        text_splitter = RecursiveCharacterTextSplitter(
            chunk_size=1000, chunk_overlap=100, separators=["\n\n", "\n", " ", ""]
        )
        return text_splitter.split_documents(documents)
