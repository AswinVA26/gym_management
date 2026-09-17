import { useState } from 'react';

const starterSuggestions = [
    'How many members do we have?',
    'What is my total revenue?',
    'Which memberships expire soon?',
    'Give me a renewal tip.',
];

export default function AiAssistant({ api, endpoint }) {
    const [messages, setMessages] = useState([
        { role: 'assistant', content: 'Hi! I\'m your gym copilot. Ask me anything about members, revenue, plans, or trainers.' },
    ]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const send = async (text) => {
        const question = (text ?? input).trim();
        if (!question || loading) {
            return;
        }

        const next = [...messages, { role: 'user', content: question }];
        setMessages(next);
        setInput('');
        setError(null);
        setLoading(true);

        try {
            const data = await api(endpoint, {
                method: 'POST',
                body: { messages: next },
            });
            setMessages([...next, { role: 'assistant', content: data.reply, source: data.source }]);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="ai-widget">
            <div className="ai-chat mb-3">
                {messages.map((message, index) => (
                    <div key={index} className={`msg ${message.role}`}>
                        {message.content}
                        {message.source && (
                            <div className="mt-1 opacity-50">
                                <i className="fas fa-microchip me-1" />
                                {message.source === 'ai' ? 'AI' : 'Built-in'}
                            </div>
                        )}
                    </div>
                ))}
                {loading && (
                    <div className="msg assistant">
                        <span className="spinner-border spinner-border-sm me-2" style={{ width: '1rem', height: '1rem' }} />
                        Thinking...
                    </div>
                )}
            </div>

            {error && <div className="alert alert-danger py-2 small">{error}</div>}

            <div className="d-flex gap-2 flex-wrap mb-2">
                {starterSuggestions.map((suggestion) => (
                    <button
                        key={suggestion}
                        type="button"
                        className="btn btn-sm btn-outline-primary"
                        onClick={() => send(suggestion)}
                        disabled={loading}
                    >
                        {suggestion}
                    </button>
                ))}
            </div>

            <form onSubmit={(e) => { e.preventDefault(); send(); }} className="d-flex gap-2">
                <input
                    className="form-control"
                    placeholder="Ask about your gym data..."
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    disabled={loading}
                />
                <button className="btn btn-success text-nowrap" type="submit" disabled={loading || !input.trim()}>
                    <i className="fas fa-paper-plane" />
                </button>
            </form>
        </div>
    );
}