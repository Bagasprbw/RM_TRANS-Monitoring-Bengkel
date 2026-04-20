<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 17h14v-5H5v5zM5 12l2-7h10l2 7"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Tambah Kendaraan</h3>
              <p class="modal-sub">Masukkan data kendaraan baru</p>
            </div>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="modal-body">
          <div class="form-group">
            <label>Merk Truk <span class="req">*</span></label>
            <div class="input-with-action">
              <select v-model="form.merk_armada_id" required>
                <option value="">— Pilih Merk —</option>
                <option v-for="m in merkArmadaList" :key="m.id" :value="m.id">
                  {{ m.nama_merk }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Jenis Truk <span class="req">*</span></label>
            <div class="input-with-action">
              <select v-model="form.jenis_armada_id" required>
                <option value="">— Pilih Jenis —</option>
                <option v-for="j in jenisArmadaList" :key="j.id" :value="j.id">
                  {{ j.nama_jenis }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Nomor Polisi <span class="req">*</span></label>
            <input v-model="form.nopol" type="text" placeholder="Contoh: B 1234 ABC" required />
            <p class="hint">Format: Kode Area, 1–4 angka, 1–3 huruf</p>
          </div>

          <transition name="fade">
            <div v-if="form.merk_armada_id && form.jenis_armada_id && form.nopol" class="preview-box">
              <div class="preview-row">
                <span class="preview-label">Merk</span>
                <span class="preview-val">{{ getMerkName(form.merk_armada_id) }}</span>
              </div>
              <div class="preview-row">
                <span class="preview-label">Jenis</span>
                <span class="preview-val">{{ getJenisName(form.jenis_armada_id) }}</span>
              </div>
              <div class="preview-row">
                <span class="preview-label">Nopol</span>
                <span class="preview-val nopol">{{ form.nopol.toUpperCase() }}</span>
              </div>
            </div>
          </transition>

          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="$emit('close')">Batal</button>
            <button type="submit" class="btn-submit" :disabled="!canSubmit">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'AddKendaraanModal',
  props: { 
    isOpen: { type: Boolean, default: false }
  },
  data() {
    return {
      form: { 
        merk_armada_id: '',
        jenis_armada_id: '', 
        nopol: '' 
      }
    }
  },
  computed: {
    ...mapGetters('armada', ['armadaList', 'jenisArmadaList', 'merkArmadaList']),
    
    canSubmit() {
      return this.form.merk_armada_id && 
             this.form.jenis_armada_id && 
             this.form.nopol.trim() &&
             /^[A-Z]{1,2}\s\d{1,4}\s[A-Z]{1,3}$/i.test(this.form.nopol.trim())
    }
  },
  watch: {
    isOpen(v) { 
      if (v) {
        this.form = { merk_armada_id: '', jenis_armada_id: '', nopol: '' }
        this.fetchJenisArmada()
        this.fetchMerkArmada()
      }
    }
  },
  methods: {
    ...mapActions('armada', ['fetchJenisArmada', 'fetchMerkArmada']),

    getMerkName(id) {
      const found = this.merkArmadaList.find(item => item.id === id)
      return found ? found.nama_merk : ''
    },

    getJenisName(id) {
      const found = this.jenisArmadaList.find(item => item.id === id)
      return found ? found.nama_jenis : ''
    },
    
    handleSubmit() {
      if (!this.canSubmit) return
      
      this.$emit('kendaraan-added', {
        nopol: this.form.nopol.trim().toUpperCase(),
        merk_armada_id: this.form.merk_armada_id,
        jenis_armada_id: this.form.jenis_armada_id
      })
    }
  }
}
</script>

<style scoped>
/* Style sama seperti sebelumnya, tetap dipertahankan */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 15, 40, 0.55);
  display: flex; align-items: center; justify-content: center;
  z-index: 50; padding: 1rem;
  backdrop-filter: blur(2px);
}

.modal-box {
  background: #fff;
  border-radius: 18px;
  width: 100%; max-width: 440px;
  box-shadow: 0 24px 64px rgba(0,0,0,0.18);
  font-family: 'Poppins', sans-serif;
  overflow: hidden;
}

.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f8;
}

.modal-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modal-icon {
  width: 40px; height: 40px;
  background: #ede9fe; color: #6d28d9;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.modal-title { font-size: 0.95rem; font-weight: 600; color: #1e1d4c; }
.modal-sub { font-size: 0.75rem; color: #9ca3af; margin-top: 1px; }

.close-btn {
  background: #f5f5fb; border: none; border-radius: 8px;
  color: #9ca3af; cursor: pointer; padding: 7px;
  display: flex; align-items: center;
  transition: all 0.15s;
}

.close-btn:hover { background: #f0f0f0; color: #374151; }

.modal-body { padding: 1.25rem 1.5rem; }

.form-group { margin-bottom: 1.1rem; }
label { display: block; font-size: 0.8rem; font-weight: 600; color: #3E3D90; margin-bottom: 6px; letter-spacing: 0.3px; text-transform: uppercase; }
.req { color: #ef4444; }

input, select {
  width: 100%; padding: 0.65rem 0.9rem;
  border: 1.5px solid #e8e8f0;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.875rem; color: #374151;
  outline: none; background: #fff;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

input:focus, select:focus {
  border-color: #3E3D90;
  box-shadow: 0 0 0 3px rgba(62,61,144,0.1);
}

input::placeholder { color: #c0c0d0; }

.hint { font-size: 0.72rem; color: #b0b0c8; margin-top: 5px; }

.preview-box {
  background: #f5f5fb;
  border: 1px solid #e8e8f0;
  border-radius: 10px;
  padding: 0.85rem 1rem;
  margin-bottom: 1.1rem;
  display: flex; flex-direction: column; gap: 6px;
}

.preview-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.83rem; }
.preview-label { color: #9ca3af; font-weight: 500; width: 50px; flex-shrink: 0; }
.preview-val { color: #1e1d4c; font-weight: 600; }
.preview-val.nopol { font-size: 0.9rem; letter-spacing: 0.5px; }

.modal-footer {
  display: flex; justify-content: flex-end; gap: 0.65rem;
  padding-top: 0.5rem;
}

.btn-cancel {
  padding: 0.6rem 1.2rem;
  background: #fff; border: 1.5px solid #e8e8f0;
  border-radius: 10px; font-family: 'Poppins', sans-serif;
  font-size: 0.83rem; font-weight: 500; color: #6b7280;
  cursor: pointer; transition: all 0.15s;
}

.btn-cancel:hover { background: #f5f5fb; border-color: #d0d0e8; }

.btn-submit {
  display: flex; align-items: center; gap: 6px;
  padding: 0.6rem 1.2rem;
  background: #3E3D90; border: none;
  border-radius: 10px; font-family: 'Poppins', sans-serif;
  font-size: 0.83rem; font-weight: 600; color: #fff;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(62,61,144,0.3);
  transition: background 0.15s, transform 0.1s;
}

.btn-submit:hover:not(:disabled) { background: #4c4bb0; transform: translateY(-1px); }
.btn-submit:disabled { background: #c7d2fe; cursor: not-allowed; box-shadow: none; transform: none; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter, .fade-leave-to { opacity: 0; }
</style>