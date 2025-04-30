"""
@file        vector_store.py
@author      MinJun Park
@date        2025-04-30
@description 벡터 스토어 모듈
             문서 청크를 임베딩 벡터로 변환하여 효율적인 검색/질의를 가능하게 하는 모듈

@update-log
"""

from typing import Any, List
from langchain_openai import OpenAIEmbeddings
from langchain_community.vectorstores import FAISS

# 벡터스토어(문서 임베딩 검색) 추상화 및 구현 모듈
# - 문서 청크를 임베딩 벡터로 변환하여 효율적으로 검색/질의할 수 있게 함
# - 다양한 벡터스토어(FAISS, Chroma 등)로 확장 가능하게 설계


class IVectorStore:
    """
    벡터스토어 추상화 인터페이스 (SRP, OCP)
    - from_documents: 문서 청크 리스트를 벡터스토어로 변환
    - as_retriever: 벡터스토어에서 검색기(retriever) 객체 반환
    """

    def from_documents(self, chunks: List[Any]) -> Any:
        raise NotImplementedError

    def as_retriever(self, store: Any) -> Any:
        raise NotImplementedError


class FaissVectorStoreAdapter(IVectorStore):
    """
    FAISS 기반 벡터스토어 구현체
    - from_documents: 문서 청크를 OpenAI 임베딩으로 벡터화 후 FAISS 인덱스 생성
    - as_retriever: FAISS 인덱스에서 k개 유사 문서 검색기 반환
    - 입력: chunks (List[Any]) - 문서 청크 리스트
    - 출력: FAISS 인덱스 객체, retriever 객체
    """

    def from_documents(self, chunks: List[Any]) -> Any:
        embeddings = OpenAIEmbeddings()
        return FAISS.from_documents(chunks, embeddings)

    def as_retriever(self, store: Any) -> Any:
        return store.as_retriever(search_kwargs={"k": 5})
