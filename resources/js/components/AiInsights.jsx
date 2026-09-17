import { useEffect, useState } from 'react';

export default function AiInsights({ api, endpoint }) {
    const [insights, setInsights] = useState([]);
    const [stats, setStats] = useState(null);
    const [source, setSource] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            const data = await api(endpoint, { method: 'GET' });
            setInsights(data.insights || []);
            setStats(data.stats || null);
            setSource(data.source || 'builtin');

            const badge = document.querySelector('#ai-insights-badge');
            if (badge) {
                badge.textContent = data.source === 'ai' ? 'AI generated' : 'Built-in';
                badge.className = `badge ${data.source === 'ai' ? 'text-bg-info' : 'text-bg-secondary'}`;
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    if (loading) {
        return (
            <div className="d-flex align-items-center gap-2 text-muted">
                <span className="spinner-border spinner-border-sm" />
                Analysing gym metrics...
            </div>
        );
    }

    return (
        <div className="ai-widget">
            {error && <div className="alert alert-danger py-2 small">{error}</div>}

            {insights.length > 0 && (
                <div className="insights-list mb-3">
                    {insights.map((insight, index) => (
                        <div key={index} className="insight-item">
                            <i className="fas fa-lightbulb me-2 text-warning" />
                            {insight}
                        </div>
                    ))}
                </div>
            )}

            {stats && (
                <div className="d-flex gap-2 flex-wrap mb-3">
                    <span className="badge text-bg-light">{stats.members_count} members</span>
                    <span className="badge text-bg-success">{stats.active_memberships} active</span>
                    <span className="badge text-bg-warning">{stats.expiring_soon} expiring ≤ 30d</span>
                    <span className="badge text-bg-info">₹{Number(stats.revenue_total || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })} revenue</span>
                    <span className="badge text-bg-light">{stats.attendance_today} check-ins today</span>
                </div>
            )}

            <button className="btn btn-sm btn-outline-primary" onClick={load}>
                <i className="fas fa-rotate me-1" /> Refresh
            </button>

            {source && (
                <span className="ai-widget-footer ms-2">
                    {source === 'ai' ? 'AI-generated' : 'Built-in engine'}
                </span>
            )}
        </div>
    );
}