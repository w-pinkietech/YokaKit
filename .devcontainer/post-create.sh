#!/bin/bash
set -e

echo "Setting up YokaKit DevContainer environment..."

npm install -g @anthropic-ai/claude-code
echo "alias dc='docker compose'" >> ~/.zshrc

echo "DevContainer setup completed successfully!"
echo "You can now start developing with YokaKit."
echo "Application available at http://localhost:18080"
