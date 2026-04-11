<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Tambah Kategori Komponen</h3>
        <button class="close-btn" @click="$emit('close')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20" height="20" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Nama Kategori Baru</label>
          <input v-model="form.nama_kategori" type="text" placeholder="Masukkan nama kategori baru" />
          <p v-if="errors.nama_kategori" class="err-msg">{{ errors.nama_kategori }}</p>
        </div>
        
        <div class="existing-box">
          <p class="existing-label">Kategori Tersedia</p>
          <div class="existing-list">
            <span v-for="cat in kategoriList" :key="cat.id" class="existing-chip">
              {{ cat.nama_kategori }}
              <button class="chip-delete" @click="handleDelete(cat.id)" title="Hapus Kategori">
                &times;
              </button>
            </span>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-cancel" @click="$emit('close')" :disabled="isSubmitting">Tutup</button>
        <button class="btn-submit" @click="submit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Menyimpan...' : 'Tambah Kategori' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import Swal from 'sweetalert2'

export default {
  name: 'AddCategoryModal',
  props: { isOpen: { type: Boolean, required: true } },
  data() {
    return {
      form: { nama_kategori: '' },
      errors: {},
      isSubmitting: false
    }
  },
  computed: {
    ...mapState('kategoriKomponen', ['kategoriList'])
  },
  watch: {
    isOpen(v) { 
      if (v) { 
        this.form = { nama_kategori: '' }; 
        this.errors = {};
      } 
    }
  },
  methods: {
    ...mapActions('kategoriKomponen', ['createKategori', 'deleteKategori']),
    async submit() {
      if (!this.form.nama_kategori.trim()) { 
        this.errors = { nama_kategori: 'Nama kategori wajib diisi' }; 
        return; 
      }
      
      this.isSubmitting = true;
      const res = await this.createKategori({
        nama_kategori: this.form.nama_kategori
      });
      this.isSubmitting = false;

      if (res.success) {
        this.form.nama_kategori = '';
        this.errors = {};
        // keep open so user can see it saved and view existing list
      }
    },
    async handleDelete(id) {
      const result = await Swal.fire({
        title: 'Hapus Kategori?',
        text: 'Anda yakin ingin menghapus kategori ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus'
      });
      if (result.isConfirmed) {
         await this.deleteKategori(id);
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay { position: fixed; inset: 0; background: rgba(15,15,40,0.55); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 440px; box-shadow: 0 24px 64px rgba(0,0,0,0.18); font-family: 'Poppins', sans-serif; overflow: hidden; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f0f0f8; }
.modal-title { font-size: 1rem; font-weight: 600; color: #1e1d4c; }
.close-btn { background: #f5f5fb; border: none; border-radius: 8px; color: #9ca3af; cursor: pointer; padding: 7px; display: flex; align-items: center; transition: all 0.15s; }
.close-btn:hover { background: #f0f0f0; color: #374151; }

.modal-body { padding: 1.1rem 1.4rem; display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
label { font-size: 0.8rem; font-weight: 600; color: #3E3D90; text-transform: uppercase; letter-spacing: 0.3px; }
input { padding: 0.65rem 0.9rem; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.875rem; outline: none; background: #fff; color: #374151; width: 100%; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; }
input:focus { border-color: #3E3D90; box-shadow: 0 0 0 3px rgba(62,61,144,0.1); }
.err-msg { font-size: 0.72rem; color: #ef4444; margin-top: 3px; }

.existing-box { background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 10px; padding: 1rem; max-height: 180px; overflow-y: auto; }
.existing-label { font-size: 0.7rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.65rem; }
.existing-list { display: flex; flex-wrap: wrap; gap: 6px; }
.existing-chip { display: inline-flex; align-items: center; gap: 5px; background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 500; border: 1px solid #c7d2fe; }
.chip-delete { background: none; border: none; font-size: 1rem; color: #818cf8; cursor: pointer; padding: 0; line-height: 1; transition: color 0.15s; margin-left: 2px; }
.chip-delete:hover { color: #ef4444; }

.modal-footer { display: flex; justify-content: flex-end; gap: 0.65rem; padding: 1rem 1.4rem; border-top: 1px solid #f0f0f8; }
.btn-cancel { padding: 0.6rem 1.1rem; background: #fff; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.15s; }
.btn-cancel:hover { background: #f5f5fb; }
.btn-submit { display: flex; align-items: center; gap: 6px; padding: 0.6rem 1.1rem; background: #3E3D90; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 600; color: #fff; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: background 0.15s, transform 0.1s; }
.btn-submit:hover:not(:disabled) { background: #4c4bb0; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
