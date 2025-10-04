(function() {
    const { render, createElement: h, useState, useEffect } = wp.element;
    const { __ } = wp.i18n;
    const apiFetch = wp.apiFetch;

    /**
     * Main App component with tabs
     */
    function App() {
        const [settings, setSettings] = useState(null);
        const [bots, setBots] = useState([]);
        const [activeTab, setActiveTab] = useState('policies');
        const [saving, setSaving] = useState(false);
        const [message, setMessage] = useState(null);

        // Load initial data
        useEffect(() => {
            // Load settings
            apiFetch({
                path: '/aipm/v1/settings',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            }).then(setSettings).catch(err => {
                console.error('Failed to load settings:', err);
                setMessage({ type: 'error', text: __('Failed to load settings', 'gatekeeper-ai') });
            });

            // Load available bots
            apiFetch({
                path: '/aipm/v1/bots',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            }).then(setBots).catch(err => {
                console.error('Failed to load bots:', err);
            });
        }, []);

        // Save settings
        const onSave = () => {
            // Validate: bot can't be in both allow and block
            const allowBots = settings.policies.global.allow || [];
            const blockBots = settings.policies.global.block || [];
            const intersection = allowBots.filter(bot => blockBots.includes(bot));
            
            if (intersection.length > 0) {
                setMessage({
                    type: 'error',
                    text: __('A bot cannot be in both Allow and Block lists', 'gatekeeper-ai')
                });
                return;
            }

            setSaving(true);
            setMessage(null);

            apiFetch({
                path: '/aipm/v1/settings',
                method: 'POST',
                headers: { 'X-WP-Nonce': GKAI.nonce },
                data: settings
            })
            .then(updatedSettings => {
                setSettings(updatedSettings);
                setMessage({
                    type: 'success',
                    text: __('Settings saved successfully', 'gatekeeper-ai')
                });
            })
            .catch(err => {
                console.error('Save failed:', err);
                setMessage({
                    type: 'error',
                    text: __('Failed to save settings', 'gatekeeper-ai')
                });
            })
            .finally(() => {
                setSaving(false);
            });
        };

        if (!settings) {
            return h('div', { className: 'gkai-loading' },
                h('p', null, __('Loading…', 'gatekeeper-ai'))
            );
        }

        return h('div', { className: 'wrap gkai-settings' },
            h('h1', null, __('Gatekeeper AI Settings', 'gatekeeper-ai')),
            
            // Tab navigation
            h('nav', { className: 'nav-tab-wrapper' },
                h('a', {
                    className: 'nav-tab' + (activeTab === 'policies' ? ' nav-tab-active' : ''),
                    href: '#policies',
                    onClick: (e) => { e.preventDefault(); setActiveTab('policies'); }
                }, __('Policies', 'gatekeeper-ai')),
                h('a', {
                    className: 'nav-tab' + (activeTab === 'c2pa' ? ' nav-tab-active' : ''),
                    href: '#c2pa',
                    onClick: (e) => { e.preventDefault(); setActiveTab('c2pa'); }
                }, __('C2PA', 'gatekeeper-ai')),
                h('a', {
                    className: 'nav-tab' + (activeTab === 'logs' ? ' nav-tab-active' : ''),
                    href: '#logs',
                    onClick: (e) => { e.preventDefault(); setActiveTab('logs'); }
                }, __('Logs', 'gatekeeper-ai'))
            ),

            // Message display
            message && h('div', {
                className: 'notice notice-' + message.type + ' is-dismissible',
                style: { marginTop: '20px' }
            },
                h('p', null, message.text),
                h('button', {
                    type: 'button',
                    className: 'notice-dismiss',
                    onClick: () => setMessage(null)
                }, h('span', { className: 'screen-reader-text' }, __('Dismiss', 'gatekeeper-ai')))
            ),

            // Tab content
            h('div', { className: 'gkai-tab-content', style: { marginTop: '20px' } },
                activeTab === 'policies' && h(PoliciesTab, { settings, setSettings, bots }),
                activeTab === 'c2pa' && h(C2PATab, { settings, setSettings }),
                activeTab === 'logs' && h(LogsTab, {})
            ),

            // Save button
            h('div', { style: { marginTop: '20px' } },
                h('button', {
                    className: 'button button-primary button-large',
                    onClick: onSave,
                    disabled: saving
                }, saving ? __('Saving…', 'gatekeeper-ai') : __('Save Settings', 'gatekeeper-ai'))
            )
        );
    }

    /**
     * Policies Tab Component
     */
    function PoliciesTab({ settings, setSettings, bots }) {
        const policies = settings.policies || { global: { allow: [], block: [] } };
        const allowBots = policies.global.allow || [];
        const blockBots = policies.global.block || [];

        const toggleBot = (botName, list) => {
            const currentList = policies.global[list] || [];
            const newList = currentList.includes(botName)
                ? currentList.filter(b => b !== botName)
                : [...currentList, botName];
            
            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    global: {
                        ...policies.global,
                        [list]: newList
                    }
                }
            });
        };

        return h('div', { className: 'gkai-policies-tab' },
            h('p', null, __('Configure global AI bot access policies. Bots in the Block list will be denied access to your content.', 'gatekeeper-ai')),
            
            h('div', { className: 'gkai-bot-lists', style: { display: 'flex', gap: '20px', marginTop: '20px' } },
                // Allow list
                h('div', { style: { flex: 1 } },
                    h('h3', null, __('Allow Bots', 'gatekeeper-ai')),
                    h('p', { className: 'description' }, __('These bots will be explicitly allowed', 'gatekeeper-ai')),
                    h('div', { className: 'gkai-bot-checkboxes' },
                        bots.map(bot => 
                            h('label', { key: bot.name, style: { display: 'block', marginBottom: '8px' } },
                                h('input', {
                                    type: 'checkbox',
                                    checked: allowBots.includes(bot.name),
                                    onChange: () => toggleBot(bot.name, 'allow')
                                }),
                                ' ',
                                bot.name,
                                bot.docs_url && h('a', {
                                    href: bot.docs_url,
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    style: { marginLeft: '8px', fontSize: '12px' }
                                }, '↗')
                            )
                        )
                    )
                ),

                // Block list
                h('div', { style: { flex: 1 } },
                    h('h3', null, __('Block Bots', 'gatekeeper-ai')),
                    h('p', { className: 'description' }, __('These bots will be denied access', 'gatekeeper-ai')),
                    h('div', { className: 'gkai-bot-checkboxes' },
                        bots.map(bot => 
                            h('label', { key: bot.name, style: { display: 'block', marginBottom: '8px' } },
                                h('input', {
                                    type: 'checkbox',
                                    checked: blockBots.includes(bot.name),
                                    onChange: () => toggleBot(bot.name, 'block')
                                }),
                                ' ',
                                bot.name,
                                bot.docs_url && h('a', {
                                    href: bot.docs_url,
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    style: { marginLeft: '8px', fontSize: '12px' }
                                }, '↗')
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * C2PA Tab Component
     */
    function C2PATab({ settings, setSettings }) {
        const c2pa = settings.c2pa || { enabled: false, ai_assisted_default: false };

        const updateC2PA = (key, value) => {
            setSettings({
                ...settings,
                c2pa: {
                    ...c2pa,
                    [key]: value
                }
            });
        };

        return h('div', { className: 'gkai-c2pa-tab' },
            h('p', null, __('Configure C2PA-Light content provenance for uploaded media.', 'gatekeeper-ai')),
            
            h('table', { className: 'form-table', role: 'presentation' },
                h('tbody', null,
                    h('tr', null,
                        h('th', { scope: 'row' },
                            h('label', { htmlFor: 'c2pa-enabled' }, __('Enable C2PA-Light', 'gatekeeper-ai'))
                        ),
                        h('td', null,
                            h('label', null,
                                h('input', {
                                    type: 'checkbox',
                                    id: 'c2pa-enabled',
                                    checked: c2pa.enabled,
                                    onChange: (e) => updateC2PA('enabled', e.target.checked)
                                }),
                                ' ',
                                __('Generate manifest files for uploaded images', 'gatekeeper-ai')
                            ),
                            h('p', { className: 'description' },
                                __('When enabled, a JSON manifest will be created for each uploaded image containing provenance information.', 'gatekeeper-ai')
                            )
                        )
                    ),
                    h('tr', null,
                        h('th', { scope: 'row' },
                            h('label', { htmlFor: 'c2pa-ai-assisted' }, __('AI-Assisted Default', 'gatekeeper-ai'))
                        ),
                        h('td', null,
                            h('label', null,
                                h('input', {
                                    type: 'checkbox',
                                    id: 'c2pa-ai-assisted',
                                    checked: c2pa.ai_assisted_default,
                                    onChange: (e) => updateC2PA('ai_assisted_default', e.target.checked)
                                }),
                                ' ',
                                __('Mark uploads as AI-assisted by default', 'gatekeeper-ai')
                            ),
                            h('p', { className: 'description' },
                                __('Check this if uploaded content is typically AI-generated or AI-enhanced.', 'gatekeeper-ai')
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Logs Tab Component (Placeholder)
     */
    function LogsTab() {
        return h('div', { className: 'gkai-logs-tab' },
            h('p', null, __('Bot access logs and analytics coming soon.', 'gatekeeper-ai')),
            h('div', { style: { padding: '40px', textAlign: 'center', backgroundColor: '#f0f0f1', borderRadius: '4px' } },
                h('span', { className: 'dashicons dashicons-clock', style: { fontSize: '48px', color: '#646970' } }),
                h('p', { style: { marginTop: '10px', color: '#646970' } }, __('This feature is under development', 'gatekeeper-ai'))
            )
        );
    }

    // Render the app
    const el = document.getElementById('gkai-app');
    if (el) {
        render(h(App), el);
    }
})();
