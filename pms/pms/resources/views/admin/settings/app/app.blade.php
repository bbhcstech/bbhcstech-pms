<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Settings</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .settings-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .section-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border-left: 4px solid #4e73df;
        }
        .section-header {
            background: #f8f9fc;
            padding: 15px 20px;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 10px 10px 0 0;
        }
        .setting-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
        }
        .setting-item:hover {
            background-color: #f9f9f9;
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-label {
            font-weight: 600;
            color: #4a4a4a;
            margin-bottom: 5px;
        }
        .setting-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #d1d3e2;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .form-switch .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
        }
        .btn-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
        }
        .hr-divider {
            border-top: 2px dashed #e3e6f0;
            margin: 20px 0;
        }
        .empty-section {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            background: #f8f9fc;
            border-radius: 10px;
            border: 2px dashed #e3e6f0;
        }
        .setting-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-sm {
            padding: 4px 12px;
            font-size: 0.85rem;
        }
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* New CSS for Navigation Tabs */
        .nav-tabs-custom {
            border-bottom: 2px solid #dee2e6;
            padding: 0;
            margin-bottom: 25px;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #495057;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 8px 8px 0 0;
            margin-bottom: -2px;
            transition: all 0.3s;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .nav-tabs-custom .nav-link.active {
            color: #0d6efd;
            background-color: white;
            border-bottom: 3px solid #0d6efd;
        }
        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <!-- Header -->
        <div class="header-card">
            <div class="row align-items-center p-4">
                <div class="col-md-6">
                    <h1 class="h3 mb-2"><i class="bi bi-gear-fill me-2"></i>{{ $pageTitle ?? 'App Settings' }}</h1>
                    <p class="mb-0">Manage all application settings dynamically</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New Field
                    </button>
                    <a href="{{ url()->current() }}" class="btn btn-outline-light">Refresh</a>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (as per your image) -->
        <div class="nav-tabs-custom">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a href="{{ route('admin.settings.app') }}"
                       class="nav-link {{ request()->is('admin/settings/app') ? 'active' : '' }}">
                        App Settings
                    </a>

                    <a href="{{ route('admin.settings.app.client-signup') }}"
                       class="nav-link {{ request()->is('admin/settings/app/client-signup') ? 'active' : '' }}">
                        Client Sign up Settings
                    </a>
                    <a href="{{ route('admin.settings.app.file-upload') }}"
                       class="nav-link {{ request()->is('admin/settings/file-upload') ? 'active' : '' }}">
                        File Upload Settings
                    </a>
                    <a href="{{ route('admin.settings.app.google-map') }}"
                       class="nav-link {{ request()->is('admin/settings/app/google-map') ? 'active' : '' }}">
                        Google Map Settings
                    </a>
                </div>
            </nav>
        </div>

        <!-- Settings Form -->
        <form action="{{ route('admin.settings.app.update') }}" method="POST" id="settingsForm">
            @csrf

            <!-- Settings Sections -->
            @if($settings->count() > 0)
                @foreach($settings as $sectionName => $sectionSettings)
                    <div class="section-card">
                        <div class="section-header">
                            <h3 class="h5 mb-0">
                                <i class="bi bi-folder me-2"></i>
                                {{ $sectionName ?: 'General Settings' }}
                                <span class="badge bg-primary ms-2">{{ count($sectionSettings) }}</span>
                            </h3>
                        </div>

                        <div class="card-body">
                            @foreach($sectionSettings as $setting)
                                <div class="setting-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="setting-label">
                                                {{ $setting->label }}
                                                @if($setting->unit)
                                                    <small class="text-muted">({{ $setting->unit }})</small>
                                                @endif
                                            </div>
                                            @if($setting->description)
                                                <div class="setting-description">
                                                    <i class="bi bi-info-circle me-1"></i>{{ $setting->description }}
                                                </div>
                                            @endif
                                            <div class="text-muted small mt-1">
                                                <code>{{ $setting->key }}</code>
                                                <span class="ms-2 badge bg-secondary">{{ $setting->type }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-8">
                                            <input type="hidden" name="settings[{{ $setting->id }}]" value="">

                                            @if($setting->type === 'select')
                                                <select name="settings[{{ $setting->id }}]" class="form-select">
                                                    <option value="">-- Select --</option>
                                                    @if($setting->options && is_array($setting->options))
                                                        @foreach($setting->options as $option)
                                                            <option value="{{ $option['value'] }}"
                                                                {{ $setting->value == $option['value'] ? 'selected' : '' }}>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>

                                            @elseif($setting->type === 'checkbox')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="settings[{{ $setting->id }}]" value="0">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="settings[{{ $setting->id }}]"
                                                           value="1" id="setting_{{ $setting->id }}"
                                                           {{ $setting->value == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="setting_{{ $setting->id }}">
                                                        {{ $setting->value == 1 ? 'Enabled' : 'Disabled' }}
                                                    </label>
                                                </div>

                                            @elseif($setting->type === 'textarea')
                                                <textarea name="settings[{{ $setting->id }}]"
                                                          class="form-control"
                                                          rows="3"
                                                          placeholder="{{ $setting->placeholder }}">{{ $setting->value }}</textarea>

                                            @else
                                                <input type="{{ $setting->type }}"
                                                       name="settings[{{ $setting->id }}]"
                                                       class="form-control"
                                                       value="{{ $setting->value }}"
                                                       placeholder="{{ $setting->placeholder }}"
                                                       @if($setting->min_value) min="{{ $setting->min_value }}" @endif
                                                       @if($setting->max_value) max="{{ $setting->max_value }}" @endif>
                                            @endif

                                            @if($setting->placeholder && $setting->type !== 'textarea')
                                                <div class="form-text">{{ $setting->placeholder }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!$loop->last)
                                        <div class="hr-divider"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Save Button -->
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Save All Changes
                    </button>
                </div>
            @else
                <div class="empty-section">
                    <i class="bi bi-gear display-4 text-muted mb-3"></i>
                    <h4>No Settings Found</h4>
                    <p class="mb-4">No settings available for this page. Add new fields to get started.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Your First Setting
                    </button>
                </div>
            @endif
        </form>

        <!-- Footer Info -->
        <div class="text-center text-muted mt-5">
            <p>App Settings Panel • All settings are stored dynamically in database</p>
        </div>
    </div>

    <!-- Add New Field Modal -->
    <div class="modal fade" id="addFieldModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.settings.app.add-field') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Setting Field</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Key *</label>
                                <input type="text" name="key" class="form-control" required
                                       placeholder="e.g., date_format, timezone">
                                <div class="form-text">Unique identifier (no spaces, use underscores)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Label *</label>
                                <input type="text" name="label" class="form-control" required
                                       placeholder="e.g., Date Format">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-select" id="fieldType" required>
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="email">Email</option>
                                    <option value="select">Dropdown Select</option>
                                    <option value="checkbox">Checkbox (On/Off)</option>
                                    <option value="textarea">Text Area</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Section *</label>
                                <select name="section" class="form-select" required>
                                    <option value="">Select Section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section }}">{{ $section }}</option>
                                    @endforeach
                                    <option value="new_section">+ Create New Section</option>
                                </select>
                                <div id="newSectionField" style="display: none; margin-top: 10px;">
                                    <input type="text" name="new_section" class="form-control"
                                           placeholder="Enter new section name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Page *</label>
                                <select name="page" class="form-select" required>
                                    <option value="app" {{ $page == 'app' ? 'selected' : '' }}>App Settings</option>
                                    <option value="client-signup" {{ $page == 'client-signup' ? 'selected' : '' }}>Client Sign up Settings</option>
                                    <option value="file-upload" {{ $page == 'file-upload' ? 'selected' : '' }}>File Upload Settings</option>
                                    <option value="google-map" {{ $page == 'google-map' ? 'selected' : '' }}>Google Map Settings</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control"
                                       value="0" placeholder="e.g., 1, 2, 3">
                            </div>
                        </div>

                        <div class="mb-3" id="optionsField" style="display: none;">
                            <label class="form-label">Dropdown Options</label>
                            <textarea name="options" class="form-control" rows="3"
                                      placeholder="Enter options separated by commas&#10;Example: Option 1, Option 2, Option 3"></textarea>
                            <div class="form-text">Options will be converted to value/label pairs automatically</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Placeholder (Optional)</label>
                            <input type="text" name="placeholder" class="form-control"
                                   placeholder="e.g., Enter date format">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Value (Optional)</label>
                            <input type="text" name="default_value" class="form-control"
                                   placeholder="Default value for this setting">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show/hide options field based on type selection
        document.getElementById('fieldType').addEventListener('change', function() {
            const optionsField = document.getElementById('optionsField');
            optionsField.style.display = this.value === 'select' ? 'block' : 'none';
        });

        // Show/hide new section field
        document.querySelector('select[name="section"]').addEventListener('change', function() {
            const newSectionField = document.getElementById('newSectionField');
            newSectionField.style.display = this.value === 'new_section' ? 'block' : 'none';
        });

        // Show success message after save
        @if(session('success'))
            alert('{{ session('success') }}');
        @endif
    </script>
</body>
</html>
