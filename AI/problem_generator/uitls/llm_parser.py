import re


def parse_llm_response(llm_text: str):
    lines = llm_text.splitlines()
    conversation_lines = []
    content_lines = []
    in_conversation = False
    in_content = False

    for line in lines:
        # [대답] 섹션 시작
        if re.match(r"^\[대답\]", line.strip()):
            in_conversation = True
            in_content = False
            continue
        # [대답] 이후 첫 [섹션]이 나오면 content로 전환
        elif in_conversation and re.match(r"^\[.+\]", line.strip()):
            in_conversation = False
            in_content = True
            content_lines.append(line)
            continue

        if in_conversation:
            conversation_lines.append(line)
        elif in_content:
            content_lines.append(line)

    conversation = "\n".join(conversation_lines).strip()
    content = "\n".join(content_lines).strip()

    if not conversation:
        conversation = "파싱 오류 또는 LLM 응답 형식 오류"
    if not content:
        content = "파싱 오류 또는 LLM 응답 형식 오류"

    return {
        "conversation": conversation,
        "content": content,
    }
