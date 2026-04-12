<template>
  <transition name="modal-fade">
    <div v-if="isOpen && component" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Edit Komponen</h3>
              <p class="modal-sub">{{ component.nama_component || 'Perbarui data komponen' }}</p>
            </div>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Kategori Komponen <span class="req">*</span></label>
            <select v-model="form.component_category_id">
              <option value="">— Pilih Kategori —</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nama_kategori }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Nama Komponen <span class="req">*</span></label>
            <input v-model="form.nama_component" type="text" placeholder="Contoh: Ban Depan Kiri" />
          </div>

          <div class="form-group">
            <label>Tipe Tracking</label>
            <div class="radio-group">
              <label class="radio-label" :class="{ active: form.tracking_type === 'km' }">
                <input type="radio" v-model="form.tracking_type" value="km" />
                <span>Berdasarkan KM</span>
              </label>
              <label class="radio-label" :class="{ active: form.tracking_type === 'date' }">
                <input type="radio" v-model="form.tracking_type" value="date" />
                <span>Berdasarkan Tanggal</span>
              </label>
            </div>
          </div>

          <div v-if="form.tracking_type === 'km'" class="form-group">
            <label>Target KM <span class="req">*</span></label>
            <input v-model.number="form.target_km" type="number" min="1" />
          </div>

          <div v-if="form.tracking_type === 'date'" class="form-group">
            <label>Durasi (Hari) <span class="req">*</span></label>
            <input v-model.number="form.target_days" type="number" min="1" />
            <div v-if="form.target_days > 0" class="date-preview">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Jatuh tempo: <strong>{{ calcTargetDate(form.target_days) }}</strong>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" @click="$emit('close')" :disabled="loading">Batal</button>
          <button class="btn-submit" @click="submit" :disabled="loading">
            <svg v-if="!loading" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import componentService from '@/modules/monitoring/services/componentService'

export default {
  name: 'EditComponentModal',
  props: {
    isOpen: { type: Boolean, default: false },
    component: { type: Object, default: null }
  },
  data() {
    return {
      loading: false,
      categories: [],
      form: { component_category_id: '', nama_component: '', tracking_type: 'km', target_km: '', target_days: '' }
    }
  },
  watch: {
    isOpen(v) {
      if (v && this.component) {
        this.loadCategories()
        this.form = {
          component_category_id: this.component.category?.id || '',
          nama_component: this.component.nama_component || '',
          tracking_type: this.component.tracking_type || 'km',
          target_km: this.component.target_km || '',
          target_days: this.component.target_days || ''
        }
      }
    }
  },
  methods: {
    loadCategories() {
      componentService.getCategories()
        .then(res => { if (res.data.success) this.categories = res.data.data })
        .catch(err => console.error('Error loading categories:', err))
    },
    submit() {
      this.loading = true
      const payload = {
        component_category_id: this.form.component_category_id,
        nama_component: this.form.nama_component,
        tracking_type: this.form.tracking_type,
        target_km: this.form.tracking_type === 'km' ? parseInt(this.form.target_km) : null,
        target_days: this.form.tracking_type === 'date' ? parseInt(this.form.target_days) : null
      }
      componentService.updateComponent(this.component.id, payload)
        .then(res => { if (res.data.success) { this.$emit('component-updated'); this.$emit('close') } })
        .catch(err => { alert(err.response?.data?.message || 'Gagal mengupdate komponen') })
        .finally(() => { this.loading = false })
    },
    calcTargetDate(days) {
      if (!days) return '-'
      const d = new Date(); d.setDate(d.getDate() + parseInt(days))
      return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.modal-overlay { position: fixed; inset: 0; background: rgba(15,15,40,0.55); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.18); font-family: 'Poppins', sans-serif; }

.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f0f0f8; position: sticky; top: 0; background: #fff; z-index: 1; }
.modal-header-left { display: flex; align-items: center; gap: 0.7rem; }
.modal-icon { width: 38px; height: 38px; background: #f0f0fb; color: #3E3D90; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-title { font-size: 0.92rem; font-weight: 600; color: #1e1d4c; }
.modal-sub { font-size: 0.73rem; color: #9ca3af; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
.close-btn { background: #f5f5fb; border: none; border-radius: 8px; color: #9ca3af; cursor: pointer; padding: 7px; display: flex; align-items: center; transition: all 0.15s; }
.close-btn:hover { background: #f0f0f0; color: #374151; }

.modal-body { padding: 1.1rem 1.4rem; display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
label { font-size: 0.8rem; font-weight: 600; color: #3E3D90; text-transform: uppercase; letter-spacing: 0.3px; }
.req { color: #ef4444; font-weight: 700; }

input[type="text"], input[type="number"], select { padding: 0.65rem 0.9rem; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.875rem; outline: none; background: #fff; color: #374151; box-sizing: border-box; width: 100%; transition: border-color 0.2s, box-shadow 0.2s; }
input:focus, select:focus { border-color: #3E3D90; box-shadow: 0 0 0 3px rgba(62,61,144,0.1); }
input::placeholder { color: #c0c0d0; }

.radio-group { display: flex; gap: 0.65rem; }
.radio-label { display: flex; align-items: center; gap: 7px; font-size: 0.83rem; color: #6b7280; cursor: pointer; font-weight: 400; padding: 0.55rem 0.9rem; border: 1.5px solid #e8e8f0; border-radius: 10px; transition: all 0.15s; flex: 1; justify-content: center; text-transform: none; letter-spacing: 0; }
.radio-label input[type="radio"] { display: none; }
.radio-label.active { border-color: #3E3D90; background: #f0f0fb; color: #3E3D90; font-weight: 600; }
.radio-label:hover:not(.active) { border-color: #c0c0e0; background: #f9f9ff; }

.date-preview { display: flex; align-items: center; gap: 6px; padding: 0.55rem 0.85rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 0.78rem; color: #1d4ed8; }

.modal-footer { display: flex; justify-content: flex-end; gap: 0.65rem; padding: 1rem 1.4rem; border-top: 1px solid #f0f0f8; position: sticky; bottom: 0; background: #fff; }
.btn-cancel { padding: 0.6rem 1.1rem; background: #fff; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.15s; }
.btn-cancel:hover { background: #f5f5fb; }
.btn-submit { display: flex; align-items: center; gap: 6px; padding: 0.6rem 1.1rem; background: #3E3D90; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 600; color: #fff; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: background 0.15s, transform 0.1s; }
.btn-submit:hover:not(:disabled) { background: #4c4bb0; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }
</style>