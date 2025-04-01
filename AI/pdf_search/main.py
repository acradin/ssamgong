from pdf_analyzer import PDFAnalyzer


def main():
    # PDF 파일 경로 설정
    pdf_path = "example.pdf"  # 실제 PDF 파일 경로로 변경 필요

    # PDF 파일 유효성 검사
    if not PDFAnalyzer.is_valid_pdf(pdf_path):
        print(f"Error: {pdf_path} is not a valid PDF file.")
        return

    try:
        # PDF 분석기 초기화
        print("PDF 분석기 초기화 중...")
        analyzer = PDFAnalyzer(pdf_path)
        print("초기화 완료")

        # 질문 루프
        while True:
            query = input("\nPDF 내용에 대해 질문하세요 (종료하려면 'q' 입력): ")
            if query.lower() == "q":
                break

            # 분석 실행
            print("\n분석 중...")
            result = analyzer.analyze_content(query)

            # 결과 출력
            print("\n분석 결과:")
            print(result)

    except Exception as e:
        print(f"Error: {str(e)}")


if __name__ == "__main__":
    main()
