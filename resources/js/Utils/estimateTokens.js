export function estimateStringTokens(content) {
    const value = (content || '').trim();

    if (!value) {
        return 0;
    }

    return Math.max(1, Math.ceil(value.length / 4));
}

export function estimateMessageTokens(messages = []) {
    return messages.reduce((total, message) => total + estimateStringTokens(message?.content || ''), 0);
}
