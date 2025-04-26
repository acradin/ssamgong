import anthropic
import os
from dotenv import load_dotenv

load_dotenv()

def run_claude(system_prompt: str, user_prompt: str) -> str:
    """
    Claude LLM에 system/user 프롬프트를 전달하고 응답 텍스트를 반환합니다.
    """
    claude_api_key = os.getenv("CLAUDE_API_KEY")
    client = anthropic.Anthropic(api_key=claude_api_key)
    message = client.messages.create(
        model="claude-3-7-sonnet-20250219",
        max_tokens=1000,
        temperature=0.7,
        system=system_prompt,
        messages=[{"role": "user", "content": user_prompt}]
    )
    # Claude 응답에서 텍스트만 추출
    if isinstance(message.content, list):
        result = "\n".join(
            block.text if hasattr(block, 'text') else block.get('text', '')
            for block in message.content
        )
    else:
        result = str(message.content)
    return result
