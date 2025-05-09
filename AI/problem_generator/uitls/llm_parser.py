import re


def parse_llm_response(llm_text: str):
    lines = llm_text.splitlines()
    title_lines = []
    content_lines = []
    in_title = False
    in_content = False

    for line in lines:
        # [제목] 섹션 시작
        if re.match(r"^\[제목\]", line.strip()):
            in_title = True
            in_content = False
            continue
        # [결과] 섹션 시작 시 content로 전환 (해당 라인은 건너뜀)
        elif in_title and re.match(r"^\[결과\]", line.strip()):
            in_title = False
            in_content = True
            continue  # [결과] 라벨은 content에 포함하지 않음

        if in_title:
            title_lines.append(line)
        elif in_content:
            content_lines.append(line)

    title = "\n".join(title_lines).strip()
    content = "\n".join(content_lines).strip()

    if not title:
        title = "파싱 오류 또는 LLM 응답 형식 오류"
    if not content:
        content = "파싱 오류 또는 LLM 응답 형식 오류"

    return {
        "title": title,
        "result": content,
    }


def parse_llm_response_to_json(llm_text: str) -> dict:
    """
    LLM 응답 전체를 result 키에 담아 JSON(dict) 형태로 반환합니다.
    """
    return {"result": llm_text}
