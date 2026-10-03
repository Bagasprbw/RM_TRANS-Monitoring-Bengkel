<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="handleClose">
      <div class="modal-box import-modal-box">

        <!-- Header -->
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon" :class="iconClass">
              <!-- Upload icon -->
              <svg v-if="currentState !== 'preview'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="17,8 12,3 7,8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              <!-- Table icon -->
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">{{ modalTitle }}</h3>
              <p class="modal-sub">{{ modalSubtitle }}</p>
            </div>
          </div>
          <button class="close-btn" @click="handleClose">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- =================== STATE 1: UPLOAD =================== -->
        <div v-if="currentState === 'upload'" class="modal-body">
          <!-- Download template -->
          <div class="template-section">
            <div class="template-info">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>Gunakan template resmi agar format data sesuai dengan sistem.</span>
            </div>
            <button class="btn-template" @click="downloadTemplate" :disabled="downloadingTemplate">
              <svg v-if="!downloadingTemplate" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              <div v-else class="spinner-xs"></div>
              {{ downloadingTemplate ? 'Mengunduh...' : 'Download Template Excel' }}
            </button>
          </div>

          <!-- Dropzone -->
          <div
            class="dropzone"
            :class="{ 'dropzone-active': isDragging, 'dropzone-filled': selectedFile }"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
            @click="$refs.fileInput.click()"
          >
            <input
              ref="fileInput"
              type="file"
              accept=".xlsx,.xls,.csv"
              style="display:none"
              @change="onFileChange"
            />
            <div v-if="!selectedFile" class="dropzone-placeholder">
              <div class="dropzone-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                  <polyline points="14,2 14,8 20,8"/>
                  <line x1="12" y1="18" x2="12" y2="12"/>
                  <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
              </div>
              <p class="dropzone-text">Drag & drop file di sini atau klik untuk pilih</p>
              <p class="dropzone-hint">Format: .xlsx, .xls, .csv &bull; Maks. 5MB</p>
            </div>
            <div v-else class="dropzone-selected">
              <div class="file-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                  <polyline points="14,2 14,8 20,8"/>
                </svg>
              </div>
              <div class="file-info">
                <p class="file-name">{{ selectedFile.name }}</p>
                <p class="file-size">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
              <button class="file-remove" @click.stop="removeFile">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Actions -->
          <div class="modal-actions">
            <button class="btn-cancel" @click="handleClose">Batal</button>
            <button class="btn-preview" @click="doPreview" :disabled="!selectedFile || previewing">
              <div v-if="previewing" class="spinner-xs"></div>
              <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
              </svg>
              {{ previewing ? 'Memvalidasi...' : 'Preview & Validasi' }}
            </button>
          </div>
        </div>

        <!-- =================== STATE 2: PREVIEW =================== -->
        <div v-if="currentState === 'preview'" class="modal-body">
          <!-- Summary badges -->
          <div class="preview-summary">
            <div class="summary-badge summary-total">
              <span class="summary-num">{{ previewResult.total_rows }}</span>
              <span class="summary-label">Total</span>
            </div>
            <div class="summary-badge summary-valid">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20,6 9,17 4,12"/>
              </svg>
              <span class="summary-num">{{ previewResult.valid_rows }}</span>
              <span class="summary-label">Valid</span>
            </div>
            <div class="summary-badge summary-error">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
              <span class="summary-num">{{ previewResult.invalid_rows }}</span>
              <span class="summary-label">Error</span>
            </div>
          </div>

          <!-- Filter tabs -->
          <div class="preview-tabs">
            <button
              v-for="tab in previewTabs"
              :key="tab.key"
              class="preview-tab"
              :class="{ active: activeTab === tab.key }"
              @click="activeTab = tab.key"
            >
              {{ tab.label }} ({{ tab.count }})
            </button>
          </div>

          <!-- Table -->
          <div class="preview-table-wrap">
            <table class="preview-table">
              <thead>
                <tr>
                  <th class="col-row">#Baris</th>
                  <th v-for="col in tableColumns" :key="col.key">{{ col.label }}</th>
                  <th class="col-status">Status</th>
                  <th class="col-error">Keterangan Error</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in filteredPreviewRows"
                  :key="row.row_number"
                  :class="row.status === 'valid' ? 'row-valid' : 'row-invalid'"
                >
                  <td class="col-row">{{ row.row_number }}</td>
                  <td v-for="col in tableColumns" :key="col.key">
                    {{ row.content[col.key] || '-' }}
                  </td>
                  <td class="col-status">
                    <span v-if="row.status === 'valid'" class="badge-valid">✓ VALID</span>
                    <span v-else class="badge-error">✗ ERROR</span>
                  </td>
                  <td class="col-error">
                    <span v-if="row.errors && row.errors.length" class="error-list">
                      {{ row.errors.join(' • ') }}
                    </span>
                    <span v-else class="text-muted-sm">-</span>
                  </td>
                </tr>
                <tr v-if="filteredPreviewRows.length === 0">
                  <td :colspan="tableColumns.length + 3" class="empty-preview">Tidak ada data</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Info partial mode -->
          <div v-if="previewResult.invalid_rows > 0 && previewResult.valid_rows > 0" class="info-partial">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>Hanya <strong>{{ previewResult.valid_rows }} baris valid</strong> yang akan disimpan. Baris error diabaikan.</span>
          </div>
          <div v-if="previewResult.invalid_rows > 0 && previewResult.valid_rows === 0" class="info-all-error">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span>Semua baris mengandung error. Perbaiki file lalu upload ulang.</span>
          </div>

          <!-- Actions -->
          <div class="modal-actions">
            <button class="btn-back" @click="backToUpload">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15,18 9,12 15,6"/>
              </svg>
              Upload Ulang
            </button>
            <button
              class="btn-execute"
              @click="doExecute"
              :disabled="previewResult.valid_rows === 0 || executing"
            >
              <div v-if="executing" class="spinner-xs"></div>
              <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20,6 9,17 4,12"/>
              </svg>
              {{ executing ? 'Menyimpan...' : `Simpan ${previewResult.valid_rows} Data Valid` }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </transition>
</template>

<script>
import axios from '@/core/axios'
import Swal from 'sweetalert2'

export default {
  name: 'ImportModal',
  props: {
    isOpen: { type: Boolean, default: false },
    /**
     * module: 'armada' | 'monitoring' | 'logkm'
     */
    module: { type: String, required: true }
  },
  emits: ['close', 'imported'],
  data() {
    return {
      currentState: 'upload',
      selectedFile: null,
      isDragging: false,
      previewing: false,
      executing: false,
      downloadingTemplate: false,
      previewResult: { total_rows: 0, valid_rows: 0, invalid_rows: 0, data: [] },
      activeTab: 'all'
    }
  },
  computed: {
    modalTitle() {
      const titles = {
        armada: 'Import Data Armada',
        monitoring: 'Import Monitoring Kendaraan',
        logkm: 'Import Log Kilometer'
      }
      return titles[this.module] || 'Import Data'
    },
    modalSubtitle() {
      if (this.currentState === 'upload') {
        const subs = {
          armada: 'Upload file Excel berisi daftar kendaraan',
          monitoring: 'Upload file Excel untuk aktivasi monitoring massal',
          logkm: 'Upload file Excel update odometer harian'
        }
        return subs[this.module] || ''
      }
      return `File: ${this.selectedFile?.name || ''}`
    },
    iconClass() {
      const classes = { armada: 'icon-purple', monitoring: 'icon-cyan', logkm: 'icon-green' }
      return classes[this.module] || 'icon-purple'
    },
    tableColumns() {
      const cols = {
        armada: [
          { key: 'nopol', label: 'Nopol' },
          { key: 'nama_merk', label: 'Merk' },
          { key: 'nama_jenis', label: 'Jenis' }
        ],
        monitoring: [
          { key: 'nopol', label: 'Nopol' },
          { key: 'km_awal', label: 'KM Awal' },
          { key: 'spedo_status', label: 'Spedo' },
          { key: 'keterangan', label: 'Keterangan' }
        ],
        logkm: [
          { key: 'nopol', label: 'Nopol' },
          { key: 'odometer_km', label: 'Odometer KM' },
          { key: 'tgl_input', label: 'Tgl Input' },
          { key: 'last_km', label: 'KM Sebelumnya' }
        ]
      }
      return cols[this.module] || []
    },
    previewTabs() {
      return [
        { key: 'all', label: 'Semua', count: this.previewResult.total_rows },
        { key: 'valid', label: '✓ Valid', count: this.previewResult.valid_rows },
        { key: 'error', label: '✗ Error', count: this.previewResult.invalid_rows }
      ]
    },
    filteredPreviewRows() {
      const rows = this.previewResult.data || []
      if (this.activeTab === 'valid') return rows.filter(r => r.status === 'valid')
      if (this.activeTab === 'error') return rows.filter(r => r.status === 'invalid')
      return rows
    },
    apiBase() {
      const bases = {
        armada: '/armada',
        monitoring: '/monitoring_armada_aktif',
        logkm: '/log_kilometer'
      }
      return bases[this.module]
    }
  },
  watch: {
    isOpen(val) {
      if (!val) this.resetState()
    }
  },
  methods: {
    handleClose() {
      if (this.previewing || this.executing) return
      this.$emit('close')
    },
    resetState() {
      this.currentState = 'upload'
      this.selectedFile = null
      this.isDragging = false
      this.previewing = false
      this.executing = false
      this.downloadingTemplate = false
      this.previewResult = { total_rows: 0, valid_rows: 0, invalid_rows: 0, data: [] }
      this.activeTab = 'all'
    },
    backToUpload() {
      this.currentState = 'upload'
      this.selectedFile = null
      this.previewResult = { total_rows: 0, valid_rows: 0, invalid_rows: 0, data: [] }
    },
    onFileChange(e) {
      const file = e.target.files[0]
      if (file) this.selectedFile = file
    },
    onDrop(e) {
      this.isDragging = false
      const file = e.dataTransfer.files[0]
      if (file) this.selectedFile = file
    },
    removeFile() {
      this.selectedFile = null
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
    },
    formatFileSize(bytes) {
      if (bytes < 1024) return bytes + ' B'
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
      return (bytes / 1024 / 1024).toFixed(2) + ' MB'
    },
    async downloadTemplate() {
      this.downloadingTemplate = true
      try {
        const resp = await axios.get(`${this.apiBase}/import-template`, {
          responseType: 'blob'
        })
        const url = window.URL.createObjectURL(new Blob([resp.data]))
        const link = document.createElement('a')
        const filenames = {
          armada: 'Template_Import_Armada.xlsx',
          monitoring: 'Template_Import_Monitoring.xlsx',
          logkm: 'Template_Import_Log_KM.xlsx'
        }
        link.href = url
        link.setAttribute('download', filenames[this.module] || 'template.xlsx')
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
      } catch (err) {
        Swal.fire('Gagal', 'Tidak dapat mengunduh template.', 'error')
      } finally {
        this.downloadingTemplate = false
      }
    },
    async doPreview() {
      if (!this.selectedFile) return
      this.previewing = true
      try {
        const formData = new FormData()
        formData.append('file', this.selectedFile)
        const resp = await axios.post(`${this.apiBase}/import-preview`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        this.previewResult = resp.data
        this.currentState = 'preview'
        this.activeTab = 'all'
      } catch (err) {
        const msg = err.response?.data?.message || 'Gagal memproses file. Pastikan format sesuai template.'
        Swal.fire({ icon: 'error', title: 'Gagal Validasi', text: msg, confirmButtonColor: '#3E3D90' })
      } finally {
        this.previewing = false
      }
    },
    async doExecute() {
      const validRows = (this.previewResult.data || [])
        .filter(r => r.status === 'valid')
        .map(r => r.content)

      if (validRows.length === 0) return

      this.executing = true
      try {
        const resp = await axios.post(`${this.apiBase}/import-execute`, { rows: validRows })
        Swal.fire({
          icon: 'success',
          title: 'Import Berhasil!',
          text: resp.data.message,
          confirmButtonColor: '#3E3D90',
          timer: 3000,
          showConfirmButton: false
        })
        this.$emit('imported', resp.data)
        this.handleClose()
      } catch (err) {
        const msg = err.response?.data?.message || 'Gagal menyimpan data.'
        Swal.fire({ icon: 'error', title: 'Import Gagal', text: msg, confirmButtonColor: '#3E3D90' })
      } finally {
        this.executing = false
      }
    }
  }
}
</script>

<style scoped>
/* ====== MODAL BASE ====== */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000; padding: 1rem;
}
.import-modal-box {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 860px;
  max-height: 90vh;
  display: flex; flex-direction: column;
  box-shadow: 0 25px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  background: #fafbff;
}
.modal-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modal-icon {
  width: 38px; height: 38px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}
.icon-purple { background: #ede9fe; color: #6d28d9; }
.icon-cyan   { background: #e0f2fe; color: #0369a1; }
.icon-green  { background: #dcfce7; color: #15803d; }
.modal-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }
.modal-sub   { font-size: 0.78rem; color: #64748b; margin: 0; }
.close-btn {
  background: none; border: none; cursor: pointer;
  color: #94a3b8; padding: 0.25rem; border-radius: 6px;
  transition: all 0.2s;
}
.close-btn:hover { background: #f1f5f9; color: #475569; }

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  display: flex; flex-direction: column; gap: 1.25rem;
}

/* ====== TEMPLATE SECTION ====== */
.template-section {
  display: flex; align-items: center; justify-content: space-between;
  background: #f8faff; border: 1px solid #e0e7ff;
  border-radius: 10px; padding: 0.85rem 1rem; gap: 1rem;
}
.template-info {
  display: flex; align-items: center; gap: 0.5rem;
  color: #4338ca; font-size: 0.82rem; flex: 1;
}
.btn-template {
  display: flex; align-items: center; gap: 0.4rem;
  background: #4F46E5; color: #fff;
  border: none; border-radius: 8px;
  padding: 0.5rem 1rem; font-size: 0.82rem; font-weight: 600;
  cursor: pointer; white-space: nowrap; transition: all 0.2s;
}
.btn-template:hover:not(:disabled) { background: #4338ca; }
.btn-template:disabled { opacity: 0.6; cursor: not-allowed; }

/* ====== DROPZONE ====== */
.dropzone {
  border: 2px dashed #c7d2fe;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #fafbff;
  min-height: 140px;
  display: flex; align-items: center; justify-content: center;
}
.dropzone:hover, .dropzone-active { border-color: #6366f1; background: #eef2ff; }
.dropzone-filled { border-color: #10b981; background: #f0fdf4; border-style: solid; }
.dropzone-placeholder { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.dropzone-icon { color: #a5b4fc; }
.dropzone-text { color: #475569; font-size: 0.9rem; font-weight: 500; margin: 0; }
.dropzone-hint { color: #94a3b8; font-size: 0.78rem; margin: 0; }
.dropzone-selected {
  display: flex; align-items: center; gap: 1rem; width: 100%;
  text-align: left;
}
.file-icon { color: #10b981; flex-shrink: 0; }
.file-info { flex: 1; }
.file-name { font-weight: 600; color: #1e293b; font-size: 0.88rem; margin: 0; word-break: break-all; }
.file-size { color: #64748b; font-size: 0.76rem; margin: 0; }
.file-remove {
  background: #fee2e2; border: none; border-radius: 6px;
  color: #ef4444; cursor: pointer; padding: 0.35rem;
  transition: all 0.2s; flex-shrink: 0;
}
.file-remove:hover { background: #fecaca; }

/* ====== MODAL ACTIONS ====== */
.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.25rem; }
.btn-cancel {
  padding: 0.6rem 1.2rem; border-radius: 8px;
  background: none; border: 1px solid #e2e8f0;
  color: #64748b; cursor: pointer; font-size: 0.85rem;
  transition: all 0.2s;
}
.btn-cancel:hover { background: #f1f5f9; }
.btn-back {
  display: flex; align-items: center; gap: 0.35rem;
  padding: 0.6rem 1.2rem; border-radius: 8px;
  background: none; border: 1px solid #e2e8f0;
  color: #475569; cursor: pointer; font-size: 0.85rem;
  transition: all 0.2s;
}
.btn-back:hover { background: #f1f5f9; }
.btn-preview {
  display: flex; align-items: center; gap: 0.4rem;
  padding: 0.6rem 1.4rem; border-radius: 8px;
  background: #3E3D90; color: #fff; border: none;
  font-size: 0.85rem; font-weight: 600; cursor: pointer;
  transition: all 0.2s;
}
.btn-preview:hover:not(:disabled) { background: #312e81; }
.btn-preview:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-execute {
  display: flex; align-items: center; gap: 0.4rem;
  padding: 0.6rem 1.4rem; border-radius: 8px;
  background: #10b981; color: #fff; border: none;
  font-size: 0.85rem; font-weight: 600; cursor: pointer;
  transition: all 0.2s;
}
.btn-execute:hover:not(:disabled) { background: #059669; }
.btn-execute:disabled { opacity: 0.5; cursor: not-allowed; }

/* ====== PREVIEW SUMMARY ====== */
.preview-summary {
  display: flex; gap: 0.75rem;
}
.summary-badge {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.6rem 1rem; border-radius: 10px; flex: 1;
}
.summary-total { background: #f1f5f9; color: #475569; }
.summary-valid { background: #dcfce7; color: #15803d; }
.summary-error { background: #fee2e2; color: #b91c1c; }
.summary-num { font-size: 1.4rem; font-weight: 800; line-height: 1; }
.summary-label { font-size: 0.75rem; font-weight: 500; }

/* ====== PREVIEW TABS ====== */
.preview-tabs { display: flex; gap: 0.5rem; border-bottom: 1px solid #e2e8f0; }
.preview-tab {
  padding: 0.5rem 1rem; border: none; background: none;
  cursor: pointer; font-size: 0.82rem; color: #64748b;
  border-bottom: 2px solid transparent; margin-bottom: -1px;
  transition: all 0.15s; font-weight: 500;
}
.preview-tab:hover { color: #3E3D90; }
.preview-tab.active { color: #3E3D90; border-bottom-color: #3E3D90; font-weight: 700; }

/* ====== PREVIEW TABLE ====== */
.preview-table-wrap { overflow-x: auto; max-height: 320px; border-radius: 8px; border: 1px solid #e2e8f0; }
.preview-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.preview-table thead th {
  background: #f8fafc; padding: 0.6rem 0.75rem;
  text-align: left; font-weight: 600; color: #475569;
  border-bottom: 1px solid #e2e8f0; white-space: nowrap;
  position: sticky; top: 0;
}
.preview-table tbody td {
  padding: 0.55rem 0.75rem;
  border-bottom: 1px solid #f1f5f9;
  color: #374151;
}
.preview-table tbody tr:last-child td { border-bottom: none; }
.row-valid { background: #f0fdf4; }
.row-invalid { background: #fff5f5; }
.col-row { width: 60px; text-align: center; color: #94a3b8; font-weight: 600; }
.col-status { width: 90px; text-align: center; }
.col-error { min-width: 200px; }
.badge-valid {
  display: inline-block; padding: 0.2rem 0.5rem; border-radius: 20px;
  background: #dcfce7; color: #15803d; font-size: 0.73rem; font-weight: 700;
}
.badge-error {
  display: inline-block; padding: 0.2rem 0.5rem; border-radius: 20px;
  background: #fee2e2; color: #b91c1c; font-size: 0.73rem; font-weight: 700;
}
.error-list { color: #b91c1c; font-size: 0.76rem; }
.text-muted-sm { color: #94a3b8; font-size: 0.76rem; }
.empty-preview { text-align: center; padding: 2rem; color: #94a3b8; }

/* ====== INFO BANNERS ====== */
.info-partial {
  display: flex; align-items: center; gap: 0.5rem;
  background: #fefce8; border: 1px solid #fde68a;
  border-radius: 8px; padding: 0.65rem 0.85rem;
  color: #854d0e; font-size: 0.82rem;
}
.info-all-error {
  display: flex; align-items: center; gap: 0.5rem;
  background: #fee2e2; border: 1px solid #fca5a5;
  border-radius: 8px; padding: 0.65rem 0.85rem;
  color: #b91c1c; font-size: 0.82rem;
}

/* ====== SPINNERS ====== */
.spinner-xs {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ====== TRANSITION ====== */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }
</style>
