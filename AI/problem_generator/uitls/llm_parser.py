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
    content = re.sub(r"^\\+", "", content)
    content = re.sub(r'\\"', '"', content)

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


import re
from typing import Dict


def parse_problem_html(text: str) -> Dict[str, str]:
    """
    입력 문자열에서 ###TITLE: 줄을 찾아 title과 content로 분리합니다.
    :param text: 전체 문제 HTML 문자열
    :return: {'title': title, 'content': content}
    """
    print(text)
    lines = text.splitlines()
    title = None
    content_lines = []
    title_found = False
    for line in lines:
        if not title_found and re.match(r"^### ?TITLE:", line.strip()):
            # 제목 추출
            title = re.sub(r"^### ?TITLE:", "", line.strip()).strip()
            title_found = True
            continue  # 제목 줄은 content에 포함하지 않음
        if title_found:
            content_lines.append(line)
    if title is None:
        raise ValueError("###TITLE: 줄을 찾을 수 없습니다.")
    content = "".join(content_lines).strip()

    return {"title": title, "content": content}
