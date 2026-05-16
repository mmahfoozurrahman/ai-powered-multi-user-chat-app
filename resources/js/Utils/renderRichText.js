import DOMPurify from 'dompurify';
import hljs from 'highlight.js/lib/core';
import css from 'highlight.js/lib/languages/css';
import javascript from 'highlight.js/lib/languages/javascript';
import php from 'highlight.js/lib/languages/php';
import sql from 'highlight.js/lib/languages/sql';
import xml from 'highlight.js/lib/languages/xml';
import MarkdownIt from 'markdown-it';

const markdown = new MarkdownIt({
    breaks: true,
    html: false,
    linkify: true,
    typographer: true,
});

hljs.registerLanguage('css', css);
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('php', php);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('xml', xml);

const escapeHtml = markdown.utils.escapeHtml;

const languageAliases = {
    html: 'xml',
    js: 'javascript',
    laravel: 'php',
    mysql: 'sql',
    vue: 'xml',
};

const resolveLanguage = (language = 'code') => {
    const normalized = (language || 'code').trim().toLowerCase();

    return languageAliases[normalized] || normalized;
};

const highlightCode = (content, language = 'code') => {
    const resolvedLanguage = resolveLanguage(language);

    if (hljs.getLanguage(resolvedLanguage)) {
        return hljs.highlight(content, { language: resolvedLanguage, ignoreIllegals: true }).value;
    }

    return escapeHtml(content);
};

const renderCodeBlock = (content, language = 'code') => {
    const safeLanguage = escapeHtml((language || 'code').trim() || 'code');
    const highlightedCode = highlightCode(content, language);

    return `
        <div class="chat-code-block">
            <div class="chat-code-block__header">
                <span class="chat-code-block__language">${safeLanguage}</span>
                <button type="button" class="chat-code-block__copy" data-copy-code>Copy code</button>
            </div>
            <pre><code class="hljs language-${safeLanguage}">${highlightedCode}</code></pre>
        </div>
    `;
};

markdown.renderer.rules.fence = (tokens, index) => {
    const token = tokens[index];
    const language = token.info?.trim().split(/\s+/)[0] || 'code';

    return renderCodeBlock(token.content, language);
};

markdown.renderer.rules.code_block = (tokens, index) => {
    return renderCodeBlock(tokens[index].content);
};

export function renderRichText(content) {
    return DOMPurify.sanitize(markdown.render(content || ''));
}
