import { useState } from 'react';

export default function AiWorkoutPlanGenerator({ api, endpoint = '/app/ai/workout-plans', members = [], preselectMember = 0 }) {
    const [memberId, setMemberId] = useState(preselectMember || '');
    const [goal, setGoal] = useState('muscle gain');
    const [level, setLevel] = useState('beginner');
    const [days, setDays] = useState(3);
    const [equipment, setEquipment] = useState('gym machines + free weights');
    const [title, setTitle] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [result, setResult] = useState(null);

    const submit = async (event) => {
        event.preventDefault();
        setError(null);

        const body = {
            member_id: memberId || null,
            goal,
            fitness_level: level,
            days_per_week: days,
            equipment,
            title: title || null,
        };

        setLoading(true);
        try {
            const data = await api(endpoint, { method: 'POST', body });
            setResult(data);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="ai-widget">
            <form onSubmit={submit} className="row g-3">
                <div className="col-md-6">
                    <label className="form-label">Member</label>
                    <select className="form-select" value={memberId} onChange={(e) => setMemberId(e.target.value)}>
                        <option value="">General / no member</option>
                        {members.map((member) => (
                            <option key={member.id} value={member.id}>
                                {member.name} ({member.code})
                            </option>
                        ))}
                    </select>
                </div>
                <div className="col-md-6">
                    <label className="form-label">Fitness goal *</label>
                    <select className="form-select" value={goal} onChange={(e) => setGoal(e.target.value)}>
                        <option>muscle gain</option>
                        <option>weight loss</option>
                        <option>strength</option>
                        <option>endurance</option>
                        <option>flexibility</option>
                        <option>general fitness</option>
                    </select>
                </div>
                <div className="col-md-4">
                    <label className="form-label">Level *</label>
                    <select className="form-select" value={level} onChange={(e) => setLevel(e.target.value)}>
                        <option>beginner</option>
                        <option>intermediate</option>
                        <option>advanced</option>
                    </select>
                </div>
                <div className="col-md-4">
                    <label className="form-label">Days / week *</label>
                    <select className="form-select" value={days} onChange={(e) => setDays(Number(e.target.value))}>
                        {[1, 2, 3, 4, 5, 6, 7].map((d) => (
                            <option key={d} value={d}>{d} day{d > 1 ? 's' : ''}</option>
                        ))}
                    </select>
                </div>
                <div className="col-md-4">
                    <label className="form-label">Equipment</label>
                    <input className="form-control" value={equipment} onChange={(e) => setEquipment(e.target.value)} />
                </div>
                <div className="col-md-6">
                    <label className="form-label">Plan title (optional)</label>
                    <input className="form-control" value={title} onChange={(e) => setTitle(e.target.value)} placeholder="e.g. Summer shred - 4 day split" />
                </div>
                <div className="col-12">
                    <button className="btn btn-primary w-100" disabled={loading}>
                        {loading ? (
                            <>
                                <span className="spinner-border spinner-border-sm me-2" /> Generating...
                            </>
                        ) : (
                            <>Generate workout plan</>
                        )}
                    </button>
                </div>
            </form>

            {error && <div className="alert alert-danger mt-3 mb-0">{error}</div>}

            {result && (
                <div className="mt-4">
                    <div className="d-flex justify-content-between align-items-center mb-2">
                        <h6 className="mb-0">{result.title}</h6>
                        <span className={`badge ${result.source === 'ai' ? 'text-bg-info' : 'text-bg-secondary'}`}>
                            {result.source === 'ai' ? 'AI generated' : 'Built-in engine'}
                        </span>
                    </div>
                    <pre className="bg-body-tertiary rounded p-3 mb-0" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word', fontFamily: 'inherit', fontSize: '.9rem' }}>
                        {result.content}
                    </pre>
                    {result.used_fallback && (
                        <div className="ai-widget-footer mt-2">
                            Generated by the local engine. Connect a free AI endpoint (Ollama / Groq / OpenRouter) for richer output.
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}