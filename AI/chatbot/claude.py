import anthropic
import os
from dotenv import load_dotenv

load_dotenv()

claude_api_key = os.getenv("CLAUDE_API_KEY")
client = anthropic.Anthropic(
    api_key=claude_api_key,  # 환경 변수를 설정했다면 생략 가능
)

# 사용자 프롬프트를 변수로 저장
system_prompt = input("시스템 프롬프트를 입력하세요: ")
user_prompt = input("사용자 프롬프트를 입력하세요: ")

message = client.messages.create(
    model="claude-3-7-sonnet-20250219",
    max_tokens = 1000,
    temperature=0.7,
    system=system_prompt,
    messages=[
        {"role": "user", "content": user_prompt}
    ]
)
print("\nClaude의 응답:")
if isinstance(message.content, list):
    for block in message.content:
        if hasattr(block, 'text'):
            print(block.text)
        elif isinstance(block, dict) and 'text' in block:
            print(block['text'])
else:
    print(message.content)
