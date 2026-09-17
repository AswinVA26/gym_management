import { createRoot } from 'react-dom/client';
import AiWorkoutPlanGenerator from './components/AiWorkoutPlanGenerator.jsx';
import AiAssistant from './components/AiAssistant.jsx';
import AiInsights from './components/AiInsights.jsx';

const registry = {
    AiWorkoutPlanGenerator,
    AiAssistant,
    AiInsights,
};

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

async function api(url, options = {}) {
    const response = await fetch(url, {
        method: options.method || 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...(options.headers || {}),
        },
        body: options.body === undefined ? undefined : JSON.stringify(options.body),
    });

    if (!response.ok) {
        let message = `Request failed (${response.status})`;
        try {
            const body = await response.json();
            message = body.message || body.error || message;
        } catch {
            // keep default message
        }
        throw new Error(message);
    }

    return response.json();
}

window.GymHub = { api };

document.querySelectorAll('[data-react-widget]').forEach((element) => {
    const name = element.getAttribute('data-react-widget');
    const Component = registry[name];

    if (!Component) {
        return;
    }

    let props = {};
    if (element.hasAttribute('data-props')) {
        try {
            props = JSON.parse(element.getAttribute('data-props'));
        } catch (error) {
            console.warn('Could not parse widget props', error);
        }
    }

    const endpoint = element.getAttribute('data-endpoint');
    if (endpoint) {
        props.endpoint = endpoint;
    }

    createRoot(element).render(<Component api={api} {...props} />);
});