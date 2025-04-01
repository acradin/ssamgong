# PDF 검색 시스템

OpenAI의 파일 검색 API를 활용한 PDF 문서 검색 시스템입니다.

## 기능

- PDF 파일 업로드 및 처리
- OpenAI API를 사용한 고급 문서 검색
- 페이지별 검색 결과 제공

## 설치 방법

1. 필요한 패키지 설치:
```bash
pip install -r requirements.txt
```

2. 환경 변수 설정:
`.env` 파일을 생성하고 OpenAI API 키를 추가합니다:
```
OPENAI_API_KEY=your_api_key_here
```

## 사용 방법

1. `main.py` 파일에서 검색하고자 하는 PDF 파일 경로를 설정합니다.
2. 프로그램을 실행합니다:
```bash
python main.py
```
3. 검색어를 입력하여 PDF 내용을 검색합니다.
4. 종료하려면 'q'를 입력합니다.

## 프로젝트 구조

- `pdf_processor.py`: PDF 파일 처리 및 텍스트 추출
- `embedding_generator.py`: OpenAI 파일 검색 API 연동
- `main.py`: 메인 실행 파일
- `requirements.txt`: 필요한 패키지 목록

## 주의사항

- OpenAI API 사용에는 비용이 발생할 수 있습니다.
- 파일 크기와 검색 횟수에 따라 API 사용량이 달라질 수 있습니다.
- API 키는 안전하게 보관해야 합니다. 