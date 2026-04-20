<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box master-modal">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon master-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Kelola Merk & Jenis</h3>
              <p class="modal-sub">Tambah atau hapus data pendukung kendaraan</p>
            </div>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        <div class="modal-body master-body">
          <div class="master-grid">
            <!-- Section Merk -->
            <div class="master-section">
              <div class="section-head">
                <h4 class="section-title">Daftar Merk</h4>
                <div class="add-inline">
                  <input v-model="newMerk" type="text" placeholder="Merk baru..." @keyup.enter="handleAddMerk" />
                  <button class="add-mini-btn" @click="handleAddMerk" :disabled="!newMerk.trim()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="3">
                      <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="master-list">
                <div v-for="m in merkArmadaList" :key="m.id" class="master-item">
                  <span>{{ m.nama_merk }}</span>
                  <button class="del-btn" @click="handleDeleteMerk(m.id)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="12" height="12" stroke-width="2.5">
                      <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Section Jenis -->
            <div class="master-section">
              <div class="section-head">
                <h4 class="section-title">Daftar Jenis</h4>
                <div class="add-inline">
                  <input v-model="newJenis" type="text" placeholder="Jenis baru..." @keyup.enter="handleAddJenis" />
                  <button class="add-mini-btn" @click="handleAddJenis" :disabled="!newJenis.trim()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="3">
                      <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="master-list">
                <div v-for="j in jenisArmadaList" :key="j.id" class="master-item">
                  <span>{{ j.nama_jenis }}</span>
                  <button class="del-btn" @click="handleDeleteJenis(j.id)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="12" height="12" stroke-width="2.5">
                      <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import Swal from 'sweetalert2'

export default {
  name: 'ManageMasterModal',
  props: {
    isOpen: { type: Boolean, default: false }
  },
  data() {
    return {
      newMerk: '',
      newJenis: ''
    }
  },
  computed: {
    ...mapGetters('armada', ['merkArmadaList', 'jenisArmadaList'])
  },
  methods: {
    ...mapActions('armada', [
      'createMerkArmada', 
      'deleteMerkArmada', 
      'createJenisArmada', 
      'deleteJenisArmada'
    ]),

    async handleAddMerk() {
      if (!this.newMerk.trim()) return
      await this.createMerkArmada({ nama_merk: this.newMerk.trim() })
      this.newMerk = ''
    },

    async handleAddJenis() {
      if (!this.newJenis.trim()) return
      await this.createJenisArmada({ nama_jenis: this.newJenis.trim() })
      this.newJenis = ''
    },

    async handleDeleteMerk(id) {
      const result = await Swal.fire({
        title: 'Hapus Merk?',
        text: 'Pastikan merk ini tidak sedang digunakan oleh kendaraan manapun.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus'
      })
      if (result.isConfirmed) {
        await this.deleteMerkArmada(id)
      }
    },

    async handleDeleteJenis(id) {
      const result = await Swal.fire({
        title: 'Hapus Jenis?',
        text: 'Pastikan jenis ini tidak sedang digunakan oleh kendaraan manapun.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus'
      })
      if (result.isConfirmed) {
        await this.deleteJenisArmada(id)
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 15, 40, 0.4);
  display: flex; align-items: center; justify-content: center;
  z-index: 60; padding: 1rem;
  backdrop-filter: blur(2px);
}

.modal-box.master-modal {
  max-width: 600px;
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  font-family: 'Poppins', sans-serif;
}

.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f8;
}

.modal-header-left { display: flex; align-items: center; gap: 0.75rem; }
.master-icon { background: #fef3c7 !important; color: #d97706 !important; }
.modal-icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

.modal-title { font-size: 1rem; font-weight: 700; color: #1e1d4c; }
.modal-sub { font-size: 0.75rem; color: #9ca3af; }

.close-btn {
  background: #f3f4f6; border: none; border-radius: 8px;
  color: #6b7280; cursor: pointer; padding: 6px;
  display: flex; align-items: center;
}

.master-body { padding: 1.5rem; }
.master-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.section-head { margin-bottom: 1rem; }
.section-title {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #3E3D90;
  font-weight: 700;
  margin-bottom: 0.75rem;
}

.add-inline {
  display: flex;
  gap: 6px;
}

.add-inline input {
  flex: 1;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  outline: none;
}

.add-inline input:focus { border-color: #3E3D90; }

.add-mini-btn {
  background: #3E3D90;
  color: #fff;
  border: none;
  border-radius: 8px;
  width: 32px;
  height: 34px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.add-mini-btn:hover:not(:disabled) { background: #4c4bb0; }
.add-mini-btn:disabled { background: #d1d5db; cursor: not-allowed; }

.master-list {
  background: #f9fafb;
  border: 1px solid #f3f4f6;
  border-radius: 12px;
  max-height: 240px;
  overflow-y: auto;
  padding: 0.5rem;
}

.master-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  background: #fff;
  border-radius: 8px;
  margin-bottom: 4px;
  font-size: 0.85rem;
  color: #374151;
  border: 1px solid transparent;
  transition: all 0.2s;
}

.master-item:hover {
  border-color: #e5e7eb;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.del-btn {
  background: #fee2e2;
  color: #ef4444;
  border: none;
  border-radius: 6px;
  padding: 5px;
  cursor: pointer;
  display: flex;
  opacity: 0;
  transition: opacity 0.2s;
}

.master-item:hover .del-btn { opacity: 1; }

.del-btn:hover { background: #fecaca; }

@media (max-width: 640px) {
  .master-grid { grid-template-columns: 1fr; }
}

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }
</style>
