<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Edit Kendaraan</h3>
              <p class="modal-sub">Perbarui data kendaraan</p>
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
            <label>Nomor Polisi <span class="req">*</span></label>
            <input v-model="form.nopol" type="text" placeholder="B 1234 ABC" required />
          </div>
          
          <div class="form-group">
            <label>Merk Truk <span class="req">*</span></label>
            <select v-model="form.merk_armada_id" required>
              <option value="" disabled>— Pilih Merk —</option>
              <option v-for="merk in merkArmadaList" :key="merk.id" :value="merk.id">
                {{ merk.nama_merk }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Jenis Truk <span class="req">*</span></label>
            <select v-model="form.jenis_armada_id" required>
              <option value="" disabled>— Pilih Jenis —</option>
              <option v-for="j in jenisArmadaList" :key="j.id" :value="j.id">
                {{ j.nama_jenis }}
              </option>
            </select>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="$emit('close')">Batal</button>
            <button type="submit" class="btn-submit">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Update
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
  name: 'EditKendaraanModal',
  props: {
    isOpen: { type: Boolean, required: true },
    kendaraan: { type: Object, default: null }
  },
  data() {
    return {
      form: { 
        nopol: '', 
        merk_armada_id: '',
        jenis_armada_id: '' 
      }
    }
  },
  computed: {
    ...mapGetters('armada', ['jenisArmadaList', 'merkArmadaList'])
  },
  watch: {
    kendaraan: {
      handler(v) {
        if (v) {
          this.form.nopol = v.nopol || ''
          this.form.merk_armada_id = v.merk_armada_id || ''
          this.form.jenis_armada_id = v.jenis_armada_id || ''
        }
      },
      immediate: true,
      deep: true
    },
    isOpen(v) {
      if (v) {
        this.fetchJenisArmada()
        this.fetchMerkArmada()
      }
    }
  },
  methods: {
    ...mapActions('armada', ['fetchJenisArmada', 'fetchMerkArmada']),
    
    handleSubmit() {
      if (!this.form.nopol || !this.form.merk_armada_id || !this.form.jenis_armada_id) return
      
      this.$emit('kendaraan-updated', {
        nopol: this.form.nopol.trim().toUpperCase(),
        merk_armada_id: this.form.merk_armada_id,
        jenis_armada_id: this.form.jenis_armada_id
      })
    }
  }
}
</script>

<style scoped>
/* Style sama seperti sebelumnya */
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
  background: #f0f0fb; color: #3E3D90;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.modal-title { font-size: 0.95rem; font-weight: 600; color: #1e1d4c; }
.modal-sub { font-size: 0.75rem; color: #9ca3af; margin-top: 1px; }

.close-btn {
  background: #f5f5fb; border: none; border-radius: 8px;
  color: #9ca3af; cursor: pointer; padding: 7px;
  display: flex; align-items: center; transition: all 0.15s;
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

.modal-footer {
  display: flex; gap: 0.65rem;
  padding-top: 0.5rem;
}

.btn-cancel {
  flex: 1; padding: 0.65rem;
  background: #fff; border: 1.5px solid #e8e8f0;
  border-radius: 10px; font-family: 'Poppins', sans-serif;
  font-size: 0.83rem; font-weight: 500; color: #6b7280;
  cursor: pointer; transition: all 0.15s;
}

.btn-cancel:hover { background: #f5f5fb; }

.btn-submit {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
  padding: 0.65rem;
  background: #3E3D90; border: none;
  border-radius: 10px; font-family: 'Poppins', sans-serif;
  font-size: 0.83rem; font-weight: 600; color: #fff;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(62,61,144,0.3);
  transition: background 0.15s, transform 0.1s;
}

.btn-submit:hover { background: #4c4bb0; transform: translateY(-1px); }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }
</style>