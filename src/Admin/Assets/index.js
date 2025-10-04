(function() {
    const { render, createElement: h, useState, useEffect, Fragment } = wp.element;
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
                const errorMsg = err.message || __('Failed to save settings', 'gatekeeper-ai');
                setMessage({
                    type: 'error',
                    text: errorMsg
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
            h('h1', null, __('Gatekeeper AI', 'gatekeeper-ai')),
            
            // Tab navigation
            h('nav', { className: 'nav-tab-wrapper' },
                ['policies', 'routes', 'per-post', 'c2pa', 'logs', 'tools', 'about'].map(tab =>
                    h('a', {
                        key: tab,
                        className: 'nav-tab' + (activeTab === tab ? ' nav-tab-active' : ''),
                        href: '#' + tab,
                        onClick: (e) => { e.preventDefault(); setActiveTab(tab); }
                    }, __(tab.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '), 'gatekeeper-ai'))
                )
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
                activeTab === 'policies' && h(PoliciesTab, { settings, setSettings, bots, setMessage }),
                activeTab === 'routes' && h(RoutesTab, { settings, setSettings, bots, setMessage }),
                activeTab === 'per-post' && h(PerPostTab, { settings, setSettings, bots, setMessage }),
                activeTab === 'c2pa' && h(C2PATab, { settings, setSettings, setMessage }),
                activeTab === 'logs' && h(LogsTab, { setMessage }),
                activeTab === 'tools' && h(ToolsTab, { settings, setSettings, bots, setMessage }),
                activeTab === 'about' && h(AboutTab, {})
            ),

            // Save button (except for tabs that don't need it)
            !['logs', 'tools', 'about'].includes(activeTab) && h('div', { style: { marginTop: '20px' } },
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
    function PoliciesTab({ settings, setSettings, bots, setMessage }) {
        const [showPreview, setShowPreview] = useState(false);
        const [previewContent, setPreviewContent] = useState('');
        const [loadingPreview, setLoadingPreview] = useState(false);

        const policies = settings.policies || { global: { allow: [], block: [] }, routes: [] };
        const allowBots = policies.global.allow || [];
        const blockBots = policies.global.block || [];

        const toggleBot = (botName, list) => {
            const currentList = policies.global[list] || [];
            const otherList = list === 'allow' ? 'block' : 'allow';
            const otherBots = policies.global[otherList] || [];

            // Remove from other list if present
            const newOtherList = otherBots.filter(b => b !== botName);
            
            const newList = currentList.includes(botName)
                ? currentList.filter(b => b !== botName)
                : [...currentList, botName];
            
            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    global: {
                        ...policies.global,
                        [list]: newList,
                        [otherList]: newOtherList
                    }
                }
            });
        };

        const applyPreset = (preset) => {
            const allBotNames = bots.map(b => b.name);
            let newGlobal = { allow: [], block: [] };

            switch (preset) {
                case 'block-all':
                    newGlobal = { allow: [], block: allBotNames };
                    break;
                case 'blog-only':
                    // Allow bots only on blog routes (handled via routes tab)
                    newGlobal = { allow: [], block: allBotNames };
                    setMessage({ type: 'info', text: __('Blocked all bots globally. Add routes in Routes tab to allow specific paths.', 'gatekeeper-ai') });
                    break;
                case 'media-only':
                    // Block only on media
                    newGlobal = { allow: allBotNames, block: [] };
                    setMessage({ type: 'info', text: __('Allowed all bots globally. Add routes in Routes tab to block media paths.', 'gatekeeper-ai') });
                    break;
                case 'custom':
                    newGlobal = { allow: [], block: [] };
                    break;
            }

            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    global: newGlobal
                }
            });
        };

        const previewRobots = () => {
            setLoadingPreview(true);
            apiFetch({
                path: '/aipm/v1/preview-robots',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            })
            .then(response => {
                setPreviewContent(response.content || '');
                setShowPreview(true);
            })
            .catch(err => {
                console.error('Preview failed:', err);
                setMessage({ type: 'error', text: __('Failed to generate preview', 'gatekeeper-ai') });
            })
            .finally(() => {
                setLoadingPreview(false);
            });
        };

        return h('div', { className: 'gkai-policies-tab' },
            h('p', null, __('Configure global AI bot access policies.', 'gatekeeper-ai')),
            
            // Presets
            h('div', { style: { marginBottom: '20px' } },
                h('h3', null, __('Quick Presets', 'gatekeeper-ai')),
                h('div', { style: { display: 'flex', gap: '10px', flexWrap: 'wrap' } },
                    h('button', {
                        className: 'button',
                        onClick: () => applyPreset('block-all')
                    }, __('Block All Bots', 'gatekeeper-ai')),
                    h('button', {
                        className: 'button',
                        onClick: () => applyPreset('blog-only')
                    }, __('Blog Only', 'gatekeeper-ai')),
                    h('button', {
                        className: 'button',
                        onClick: () => applyPreset('media-only')
                    }, __('Block Media Only', 'gatekeeper-ai')),
                    h('button', {
                        className: 'button',
                        onClick: () => applyPreset('custom')
                    }, __('Custom (Clear)', 'gatekeeper-ai'))
                )
            ),

            // Bot lists
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
            ),

            // Preview button
            h('div', { style: { marginTop: '20px' } },
                h('button', {
                    className: 'button button-secondary',
                    onClick: previewRobots,
                    disabled: loadingPreview
                }, loadingPreview ? __('Loading…', 'gatekeeper-ai') : __('Preview robots.txt', 'gatekeeper-ai'))
            ),

            // Preview modal
            showPreview && h('div', {
                className: 'gkai-modal-overlay',
                onClick: () => setShowPreview(false),
                style: {
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 100000
                }
            },
                h('div', {
                    className: 'gkai-modal-content',
                    onClick: (e) => e.stopPropagation(),
                    style: {
                        backgroundColor: 'white',
                        padding: '20px',
                        borderRadius: '4px',
                        maxWidth: '800px',
                        maxHeight: '80vh',
                        overflow: 'auto',
                        boxShadow: '0 2px 10px rgba(0,0,0,0.3)'
                    }
                },
                    h('h2', null, __('robots.txt Preview', 'gatekeeper-ai')),
                    h('pre', { style: { backgroundColor: '#f5f5f5', padding: '15px', overflow: 'auto' } }, previewContent),
                    h('div', { style: { marginTop: '15px', display: 'flex', gap: '10px' } },
                        h('button', {
                            className: 'button button-primary',
                            onClick: () => {
                                navigator.clipboard.writeText(previewContent);
                                setMessage({ type: 'success', text: __('Copied to clipboard', 'gatekeeper-ai') });
                            }
                        }, __('Copy', 'gatekeeper-ai')),
                        h('button', {
                            className: 'button',
                            onClick: () => setShowPreview(false)
                        }, __('Close', 'gatekeeper-ai'))
                    )
                )
            )
        );
    }

    /**
     * Routes Tab Component
     */
    function RoutesTab({ settings, setSettings, bots, setMessage }) {
        const policies = settings.policies || { routes: [] };
        const routes = policies.routes || [];

        const addRoute = () => {
            const newRoute = { pattern: '', allow: [], block: [] };
            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    routes: [...routes, newRoute]
                }
            });
        };

        const updateRoute = (index, field, value) => {
            const newRoutes = [...routes];
            newRoutes[index] = { ...newRoutes[index], [field]: value };
            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    routes: newRoutes
                }
            });
        };

        const removeRoute = (index) => {
            const newRoutes = routes.filter((_, i) => i !== index);
            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    routes: newRoutes
                }
            });
        };

        const toggleRouteBot = (routeIndex, botName, list) => {
            const route = routes[routeIndex];
            const currentList = route[list] || [];
            const otherList = list === 'allow' ? 'block' : 'allow';
            const otherBots = route[otherList] || [];

            const newOtherList = otherBots.filter(b => b !== botName);
            const newList = currentList.includes(botName)
                ? currentList.filter(b => b !== botName)
                : [...currentList, botName];

            const newRoutes = [...routes];
            newRoutes[routeIndex] = {
                ...route,
                [list]: newList,
                [otherList]: newOtherList
            };

            setSettings({
                ...settings,
                policies: {
                    ...policies,
                    routes: newRoutes
                }
            });
        };

        return h('div', { className: 'gkai-routes-tab' },
            h('p', null, __('Define route-specific bot access rules. Routes override global policies. Use * as wildcard.', 'gatekeeper-ai')),
            
            h('button', {
                className: 'button button-secondary',
                onClick: addRoute,
                style: { marginBottom: '15px' }
            }, __('Add Route', 'gatekeeper-ai')),

            routes.length === 0 && h('p', { style: { fontStyle: 'italic', color: '#666' } },
                __('No routes defined yet. Click "Add Route" to create one.', 'gatekeeper-ai')
            ),

            routes.map((route, idx) =>
                h('div', {
                    key: idx,
                    style: {
                        border: '1px solid #ccc',
                        padding: '15px',
                        marginBottom: '15px',
                        borderRadius: '4px',
                        backgroundColor: '#f9f9f9'
                    }
                },
                    h('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } },
                        h('strong', null, __('Route', 'gatekeeper-ai') + ' #' + (idx + 1)),
                        h('button', {
                            className: 'button button-link-delete',
                            onClick: () => removeRoute(idx)
                        }, __('Remove', 'gatekeeper-ai'))
                    ),
                    
                    h('div', { style: { marginBottom: '10px' } },
                        h('label', null,
                            h('strong', null, __('Pattern:', 'gatekeeper-ai')),
                            ' ',
                            h('input', {
                                type: 'text',
                                value: route.pattern,
                                onChange: (e) => updateRoute(idx, 'pattern', e.target.value),
                                placeholder: '/blog/* or /wp-content/uploads/*',
                                style: { width: '100%', marginTop: '5px' }
                            }),
                            h('small', { style: { display: 'block', marginTop: '5px', color: '#666' } },
                                __('Use * as wildcard. Examples: /blog/*, /uploads/*.pdf', 'gatekeeper-ai')
                            )
                        )
                    ),

                    h('div', { style: { display: 'flex', gap: '20px' } },
                        h('div', { style: { flex: 1 } },
                            h('strong', null, __('Allow:', 'gatekeeper-ai')),
                            bots.map(bot =>
                                h('label', { key: bot.name, style: { display: 'block', marginTop: '5px' } },
                                    h('input', {
                                        type: 'checkbox',
                                        checked: (route.allow || []).includes(bot.name),
                                        onChange: () => toggleRouteBot(idx, bot.name, 'allow')
                                    }),
                                    ' ', bot.name
                                )
                            )
                        ),
                        h('div', { style: { flex: 1 } },
                            h('strong', null, __('Block:', 'gatekeeper-ai')),
                            bots.map(bot =>
                                h('label', { key: bot.name, style: { display: 'block', marginTop: '5px' } },
                                    h('input', {
                                        type: 'checkbox',
                                        checked: (route.block || []).includes(bot.name),
                                        onChange: () => toggleRouteBot(idx, bot.name, 'block')
                                    }),
                                    ' ', bot.name
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Per-Post Tab Component
     */
    function PerPostTab({ settings, setSettings, bots, setMessage }) {
        return h('div', { className: 'gkai-perpost-tab' },
            h('p', null, __('Per-post overrides are configured via the post editor metabox.', 'gatekeeper-ai')),
            h('div', { style: { padding: '20px', backgroundColor: '#f0f0f1', borderRadius: '4px' } },
                h('p', null, __('To set bot access rules for individual posts:', 'gatekeeper-ai')),
                h('ol', null,
                    h('li', null, __('Edit any post or page', 'gatekeeper-ai')),
                    h('li', null, __('Find the "Bot Access Policy" metabox in the sidebar', 'gatekeeper-ai')),
                    h('li', null, __('Choose a general policy and select specific bots', 'gatekeeper-ai')),
                    h('li', null, __('Per-post rules override route and global policies', 'gatekeeper-ai'))
                ),
                h('p', { style: { marginTop: '15px', fontStyle: 'italic' } },
                    __('Per-post overrides have the highest priority in the merge logic.', 'gatekeeper-ai')
                )
            )
        );
    }

    /**
     * C2PA Tab Component
     */
    function C2PATab({ settings, setSettings, setMessage }) {
        const [scanning, setScanning] = useState(false);
        const [scanResults, setScanResults] = useState(null);

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

        const scanMediaLibrary = () => {
            setScanning(true);
            setScanResults(null);
            
            apiFetch({
                path: '/aipm/v1/c2pa/scan?limit=20',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            })
            .then(results => {
                setScanResults(results);
            })
            .catch(err => {
                console.error('Scan failed:', err);
                setMessage({ type: 'error', text: __('Failed to scan media library', 'gatekeeper-ai') });
            })
            .finally(() => {
                setScanning(false);
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
                                __('When enabled, a JSON manifest will be created for each uploaded image.', 'gatekeeper-ai')
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
                            )
                        )
                    )
                )
            ),

            h('hr'),

            h('h3', null, __('Media Library Scan', 'gatekeeper-ai')),
            h('p', null, __('Scan random images to check which ones have C2PA manifests.', 'gatekeeper-ai')),
            h('button', {
                className: 'button button-secondary',
                onClick: scanMediaLibrary,
                disabled: scanning
            }, scanning ? __('Scanning…', 'gatekeeper-ai') : __('Scan Media Library', 'gatekeeper-ai')),

            scanResults && h('div', { style: { marginTop: '20px' } },
                h('h4', null, __('Scan Results', 'gatekeeper-ai') + ': ' + scanResults.total_scanned + ' ' + __('images', 'gatekeeper-ai')),
                h('table', { className: 'wp-list-table widefat fixed striped' },
                    h('thead', null,
                        h('tr', null,
                            h('th', null, __('ID', 'gatekeeper-ai')),
                            h('th', null, __('Title', 'gatekeeper-ai')),
                            h('th', null, __('Uploaded', 'gatekeeper-ai')),
                            h('th', null, __('Manifest', 'gatekeeper-ai'))
                        )
                    ),
                    h('tbody', null,
                        scanResults.attachments.map(att =>
                            h('tr', { key: att.id },
                                h('td', null, att.id),
                                h('td', null, att.title),
                                h('td', null, att.uploaded),
                                h('td', null, att.has_manifest ? '✓' : '—')
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Logs Tab Component
     */
    function LogsTab({ setMessage }) {
        const [logsEnabled, setLogsEnabled] = useState(false);
        const [logs, setLogs] = useState([]);
        const [stats, setStats] = useState(null);
        const [filters, setFilters] = useState({ bot: '', days: 7 });
        const [loading, setLoading] = useState(false);

        useEffect(() => {
            // Load settings to get logging enabled status
            apiFetch({
                path: '/aipm/v1/settings',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            }).then(settings => {
                setLogsEnabled(settings.logging?.enabled || false);
            });

            loadLogs();
            loadStats();
        }, []);

        const loadLogs = () => {
            setLoading(true);
            apiFetch({
                path: '/aipm/v1/logs?limit=200',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            })
            .then(setLogs)
            .catch(err => console.error('Failed to load logs:', err))
            .finally(() => setLoading(false));
        };

        const loadStats = () => {
            apiFetch({
                path: '/aipm/v1/logs/stats?days=' + filters.days,
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            })
            .then(setStats)
            .catch(err => console.error('Failed to load stats:', err));
        };

        const toggleLogging = () => {
            const newValue = !logsEnabled;
            apiFetch({
                path: '/aipm/v1/settings',
                method: 'POST',
                headers: { 'X-WP-Nonce': GKAI.nonce },
                data: { logging: { enabled: newValue } }
            })
            .then(() => {
                setLogsEnabled(newValue);
                setMessage({ type: 'success', text: newValue ? __('Logging enabled', 'gatekeeper-ai') : __('Logging disabled', 'gatekeeper-ai') });
            })
            .catch(err => {
                console.error('Failed to toggle logging:', err);
                setMessage({ type: 'error', text: __('Failed to update settings', 'gatekeeper-ai') });
            });
        };

        const clearLogs = () => {
            if (!confirm(__('Are you sure you want to clear all logs?', 'gatekeeper-ai'))) {
                return;
            }

            apiFetch({
                path: '/aipm/v1/logs/clear',
                method: 'POST',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            })
            .then(() => {
                setLogs([]);
                loadStats();
                setMessage({ type: 'success', text: __('Logs cleared', 'gatekeeper-ai') });
            })
            .catch(err => {
                console.error('Failed to clear logs:', err);
                setMessage({ type: 'error', text: __('Failed to clear logs', 'gatekeeper-ai') });
            });
        };

        return h('div', { className: 'gkai-logs-tab' },
            h('div', { style: { marginBottom: '20px' } },
                h('label', null,
                    h('input', {
                        type: 'checkbox',
                        checked: logsEnabled,
                        onChange: toggleLogging
                    }),
                    ' ',
                    h('strong', null, __('Enable Logging', 'gatekeeper-ai'))
                ),
                h('p', { className: 'description' },
                    __('Logs bot access attempts. No personal data is stored (GDPR-friendly).', 'gatekeeper-ai')
                )
            ),

            stats && h('div', { style: { marginBottom: '20px', padding: '15px', backgroundColor: '#f0f0f1', borderRadius: '4px' } },
                h('h3', null, __('Statistics', 'gatekeeper-ai') + ' (' + stats.days + ' ' + __('days', 'gatekeeper-ai') + ')'),
                h('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' } },
                    h('div', null,
                        h('h4', null, __('Top Bots', 'gatekeeper-ai')),
                        h('ul', null,
                            Object.entries(stats.top_bots || {}).map(([bot, count]) =>
                                h('li', { key: bot }, bot + ': ' + count)
                            )
                        )
                    ),
                    h('div', null,
                        h('h4', null, __('Results', 'gatekeeper-ai')),
                        h('ul', null,
                            h('li', null, __('Allowed', 'gatekeeper-ai') + ': ' + (stats.results.allow || 0)),
                            h('li', null, __('Blocked', 'gatekeeper-ai') + ': ' + (stats.results.block || 0))
                        )
                    )
                )
            ),

            h('div', { style: { marginBottom: '15px', display: 'flex', gap: '10px' } },
                h('button', {
                    className: 'button button-secondary',
                    onClick: loadLogs
                }, __('Refresh', 'gatekeeper-ai')),
                h('button', {
                    className: 'button button-link-delete',
                    onClick: clearLogs
                }, __('Clear Logs', 'gatekeeper-ai'))
            ),

            loading && h('p', null, __('Loading…', 'gatekeeper-ai')),

            !loading && logs.length === 0 && h('p', { style: { fontStyle: 'italic' } },
                __('No logs yet.', 'gatekeeper-ai')
            ),

            !loading && logs.length > 0 && h('table', { className: 'wp-list-table widefat fixed striped' },
                h('thead', null,
                    h('tr', null,
                        h('th', null, __('Time', 'gatekeeper-ai')),
                        h('th', null, __('Path', 'gatekeeper-ai')),
                        h('th', null, __('Bot', 'gatekeeper-ai')),
                        h('th', null, __('Result', 'gatekeeper-ai')),
                        h('th', null, __('Source', 'gatekeeper-ai'))
                    )
                ),
                h('tbody', null,
                    logs.map((log, idx) =>
                        h('tr', { key: idx },
                            h('td', null, log.timestamp),
                            h('td', null, log.path),
                            h('td', null, log.bot),
                            h('td', null, h('span', {
                                style: {
                                    color: log.result === 'allow' ? 'green' : 'red',
                                    fontWeight: 'bold'
                                }
                            }, log.result)),
                            h('td', null, log.source)
                        )
                    )
                )
            )
        );
    }

    /**
     * Tools Tab Component
     */
    function ToolsTab({ settings, setSettings, bots, setMessage }) {
        const [testPath, setTestPath] = useState('/');
        const [testBot, setTestBot] = useState('');
        const [testResult, setTestResult] = useState(null);
        const [testLoading, setTestLoading] = useState(false);

        const testMerge = () => {
            setTestLoading(true);
            setTestResult(null);

            apiFetch({
                path: '/aipm/v1/tools/test-merge',
                method: 'POST',
                headers: { 'X-WP-Nonce': GKAI.nonce },
                data: { path: testPath, post_id: null }
            })
            .then(result => {
                setTestResult(result);
            })
            .catch(err => {
                console.error('Test failed:', err);
                setMessage({ type: 'error', text: __('Test failed', 'gatekeeper-ai') });
            })
            .finally(() => {
                setTestLoading(false);
            });
        };

        const exportPolicies = () => {
            apiFetch({
                path: '/aipm/v1/tools/export',
                method: 'GET',
                headers: { 'X-WP-Nonce': GKAI.nonce }
            })
            .then(data => {
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'gatekeeper-ai-policies-' + new Date().toISOString().split('T')[0] + '.json';
                a.click();
                URL.revokeObjectURL(url);
                setMessage({ type: 'success', text: __('Policies exported', 'gatekeeper-ai') });
            })
            .catch(err => {
                console.error('Export failed:', err);
                setMessage({ type: 'error', text: __('Export failed', 'gatekeeper-ai') });
            });
        };

        const importPolicies = (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (evt) => {
                try {
                    const data = JSON.parse(evt.target.result);
                    
                    apiFetch({
                        path: '/aipm/v1/tools/import',
                        method: 'POST',
                        headers: { 'X-WP-Nonce': GKAI.nonce },
                        data: data
                    })
                    .then(result => {
                        setMessage({ type: 'success', text: result.message || __('Policies imported', 'gatekeeper-ai') });
                        // Reload settings
                        apiFetch({
                            path: '/aipm/v1/settings',
                            method: 'GET',
                            headers: { 'X-WP-Nonce': GKAI.nonce }
                        }).then(setSettings);
                    })
                    .catch(err => {
                        console.error('Import failed:', err);
                        const errorMsg = err.message || __('Import failed', 'gatekeeper-ai');
                        setMessage({ type: 'error', text: errorMsg });
                    });
                } catch (err) {
                    setMessage({ type: 'error', text: __('Invalid JSON file', 'gatekeeper-ai') });
                }
            };
            reader.readAsText(file);
            e.target.value = ''; // Reset input
        };

        return h('div', { className: 'gkai-tools-tab' },
            h('h3', null, __('Test Bot Access', 'gatekeeper-ai')),
            h('p', null, __('Test how policies would apply to a specific path.', 'gatekeeper-ai')),
            
            h('div', { style: { marginBottom: '20px' } },
                h('label', null,
                    __('Path:', 'gatekeeper-ai'),
                    h('input', {
                        type: 'text',
                        value: testPath,
                        onChange: (e) => setTestPath(e.target.value),
                        placeholder: '/blog/my-post',
                        style: { width: '300px', marginLeft: '10px' }
                    })
                ),
                h('button', {
                    className: 'button button-secondary',
                    onClick: testMerge,
                    disabled: testLoading,
                    style: { marginLeft: '10px' }
                }, testLoading ? __('Testing…', 'gatekeeper-ai') : __('Test', 'gatekeeper-ai'))
            ),

            testResult && h('div', { style: { padding: '15px', backgroundColor: '#f0f0f1', borderRadius: '4px', marginBottom: '20px' } },
                h('h4', null, __('Test Results', 'gatekeeper-ai')),
                h('pre', { style: { backgroundColor: 'white', padding: '10px', overflow: 'auto' } },
                    JSON.stringify(testResult, null, 2)
                )
            ),

            h('hr'),

            h('h3', null, __('Import/Export Policies', 'gatekeeper-ai')),
            h('p', null, __('Backup or transfer your bot access policies.', 'gatekeeper-ai')),
            
            h('div', { style: { display: 'flex', gap: '10px', marginTop: '15px' } },
                h('button', {
                    className: 'button button-secondary',
                    onClick: exportPolicies
                }, __('Export Policies', 'gatekeeper-ai')),
                
                h('label', { className: 'button button-secondary', style: { cursor: 'pointer' } },
                    __('Import Policies', 'gatekeeper-ai'),
                    h('input', {
                        type: 'file',
                        accept: '.json',
                        onChange: importPolicies,
                        style: { display: 'none' }
                    })
                )
            )
        );
    }

    /**
     * About Tab Component
     */
    function AboutTab() {
        return h('div', { className: 'gkai-about-tab' },
            h('div', { style: { maxWidth: '800px' } },
                h('h2', null, 'Gatekeeper AI'),
                h('p', { style: { fontSize: '18px', marginBottom: '20px' } },
                    __('Developed by', 'gatekeeper-ai') + ' ',
                    h('a', { href: 'https://kikraft.at/', target: '_blank', rel: 'noopener noreferrer' }, 'ki Kraft')
                ),
                
                h('h3', null, __('About ki Kraft', 'gatekeeper-ai')),
                h('p', null,
                    __('ki Kraft is a non-profit organization based in Austria, dedicated to promoting ethical and transparent use of AI technology. We believe in empowering website owners with tools to control how AI bots access and use their content.', 'gatekeeper-ai')
                ),

                h('h3', null, __('Privacy & Data Protection', 'gatekeeper-ai')),
                h('p', null,
                    __('Gatekeeper AI is designed with privacy in mind:', 'gatekeeper-ai')
                ),
                h('ul', null,
                    h('li', null, __('No telemetry or external data collection', 'gatekeeper-ai')),
                    h('li', null, __('Optional logging stores no personal information (GDPR-compliant)', 'gatekeeper-ai')),
                    h('li', null, __('All data stays on your WordPress installation', 'gatekeeper-ai')),
                    h('li', null, __('Logs contain only: timestamp, URL path, bot name, and access decision', 'gatekeeper-ai'))
                ),

                h('h3', null, __('Features', 'gatekeeper-ai')),
                h('ul', null,
                    h('li', null, __('Control AI bot access via robots.txt, meta tags, and HTTP headers', 'gatekeeper-ai')),
                    h('li', null, __('Global, route-based, and per-post policies', 'gatekeeper-ai')),
                    h('li', null, __('C2PA-Light content provenance manifests', 'gatekeeper-ai')),
                    h('li', null, __('Access logging and analytics', 'gatekeeper-ai')),
                    h('li', null, __('Import/Export policy configurations', 'gatekeeper-ai'))
                ),

                h('div', { style: { marginTop: '30px', padding: '20px', backgroundColor: '#f0f0f1', borderRadius: '4px' } },
                    h('p', { style: { margin: 0 } },
                        h('strong', null, __('Learn more:', 'gatekeeper-ai') + ' '),
                        h('a', { href: 'https://kikraft.at/', target: '_blank', rel: 'noopener noreferrer' }, 'kikraft.at')
                    )
                )
            )
        );
    }

    // Render the app
    const el = document.getElementById('gkai-app');
    if (el) {
        render(h(App), el);
    }
})();
