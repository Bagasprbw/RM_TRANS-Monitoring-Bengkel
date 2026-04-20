<template>
  <transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
              <h3 class="modal-title">Edit Komponen</h3>
              <p class="modal-sub">Ubah Pengaturan Komponen Ini</p>
            </div>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div class="modal-body">
          <!-- Basic Info -->
          <div class="form-group">
            <label>Kategori Komponen <span class="req">*</span></label>
            <select v-model="form.kategori_komponen_id" :class="{ 'input-error': errors.kategori_komponen_id }">
              <option value="">— Pilih Kategori —</option>
              <option v-for="cat in kategoriList" :key="cat.id" :value="cat.id">{{ cat.nama_kategori }}</option>
            </select>
            <p v-if="errors.kategori_komponen_id" class="field-error">{{ errors.kategori_komponen_id }}</p>
          </div>

          <div class="form-group">
            <label>Nama Komponen <span class="req">*</span></label>
            <input v-model="form.nama_komponen" type="text" placeholder="Contoh: Ban Depan Kiri" :class="{ 'input-error': errors.nama_komponen }" />
            <p v-if="errors.nama_komponen" class="field-error">{{ errors.nama_komponen }}</p>
          </div>

          <div class="form-group">
            <label>Tipe Tracking <span class="req">*</span></label>
            <div class="radio-group">
              <label class="radio-label" :class="{ active: form.tipe_pelacakan === 'km' }">
                <input type="radio" v-model="form.tipe_pelacakan" value="km" />
                <span>KM</span>
              </label>
              <label class="radio-label" :class="{ active: form.tipe_pelacakan === 'days' }">
                <input type="radio" v-model="form.tipe_pelacakan" value="days" />
                <span>Hari</span>
              </label>
              <label class="radio-label" :class="{ active: form.tipe_pelacakan === 'date' }">
                <input type="radio" v-model="form.tipe_pelacakan" value="date" />
                <span>Tanggal</span>
              </label>
            </div>
          </div>

          <div v-if="form.tipe_pelacakan === 'km'" class="form-group fade-in">
            <label>Target KM <span class="req">*</span></label>
            <input v-model.number="form.target_km" type="number" min="1" placeholder="Contoh: 10000" :class="{ 'input-error': errors.target_km }" />
            <p class="hint">Komponen perlu servis setelah sekian km</p>
            <p v-if="errors.target_km" class="field-error">{{ errors.target_km }}</p>
          </div>

          <div v-if="form.tipe_pelacakan === 'days'" class="form-group fade-in">
            <label>Durasi (Setiap Berapa Hari) <span class="req">*</span></label>
            <input v-model.number="form.target_hari" type="number" min="1" placeholder="Contoh: 365" :class="{ 'input-error': errors.target_hari }" />
            <p class="hint">Servis rutin setiap {{ form.target_hari || 0 }} hari sekali</p>
            <p v-if="errors.target_hari" class="field-error">{{ errors.target_hari }}</p>
          </div>

          <div v-if="form.tipe_pelacakan === 'date'" class="form-group fade-in">
            <label>Tanggal Jatuh Tempo <span class="req">*</span></label>
            <input v-model="form.target_tanggal" type="date" :class="{ 'input-error': errors.target_tanggal }" />
            <p class="hint">Misal: Masa berlaku Pajak/STNK</p>
            <p v-if="errors.target_tanggal" class="field-error">{{ errors.target_tanggal }}</p>
          </div>

          <!-- Identity Toggle -->
          <div class="identity-section">
            <div class="toggle-row">
              <label class="toggle-label">
                <span class="toggle-title">Memiliki Identitas Fisik?</span>
                <span class="toggle-sub">Gunakan ini jika ingin mencatat No Seri, No Stamp, dsb.</span>
              </label>
              <div class="switch" @click="form.has_identity = !form.has_identity" :class="{ active: form.has_identity }">
                <div class="switch-handle"></div>
              </div>
            </div>

            <!-- Identity Forms -->
            <transition name="slide-fade">
              <div v-if="form.has_identity" class="identity-form-box">
                <div class="identity-header">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                  <span>Detail Identitas {{ selectedCategoryName }}</span>
                </div>
                
                <div class="grid-form">
                  <div class="form-group">
                    <label>Nomor Seri</label>
                    <input v-model="form.detail.nomor_seri" type="text" placeholder="No Seri / Serial Number" />
                  </div>
                  
                  <!-- Special fields for Ban -->
                  <template v-if="isBanCategory">
                    <div class="form-group">
                      <label>Nomor Stamp</label>
                      <input v-model="form.detail.nomor_stamp" type="text" placeholder="No Stamp" />
                    </div>
                    <div class="form-group">
                      <label>Jenis Ban</label>
                      <select v-model="form.detail.jenis_ban">
                        <option value="ORI">ORI</option>
                        <option value="VULK">VULK</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Ukuran</label>
                      <input v-model="form.detail.ukuran" type="text" placeholder="Misal: 11 R 22.5" />
                    </div>
                  </template>

                  <div class="form-group">
                    <label>Merk / Tipe</label>
                    <input v-model="form.detail.merk_tipe" type="text" placeholder="Misal: Bridgestone" />
                  </div>

                  <div class="form-group">
                    <label>Supplier / Pemasok</label>
                    <input v-model="form.detail.pemasok" type="text" />
                  </div>

                  <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input v-model.number="form.detail.harga" type="number" />
                  </div>

                  <div class="form-group">
                    <label>Tanggal Pasang</label>
                    <input v-model="form.detail.tanggal_pemasangan" type="date" />
                  </div>

                  <div v-if="isBanCategory" class="form-group">
                    <label>KM Pasang</label>
                    <input v-model.number="form.detail.km_pemasangan" type="number" />
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" @click="$emit('close')" :disabled="loading">Batal</button>
          <button class="btn-submit" @click="submit" :disabled="loading">
            <svg v-if="!loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import componentService from '@/modules/monitoring/services/componentService'

export default {
  name: 'EditComponentModal',
  props: {
    isOpen: { type: Boolean, default: false },
    componentData: { type: Object, default: () => null }
  },
  data() {
    return {
      loading: false,
      form: { 
        kategori_komponen_id: '', 
        nama_komponen: '', 
        tipe_pelacakan: 'km', 
        target_km: '', 
        target_hari: '', 
        target_tanggal: '',
        has_identity: false,
        detail: {
            nomor_seri: '',
            nomor_stamp: '',
            jenis_ban: 'ORI',
            ukuran: '',
            merk_tipe: '',
            pemasok: '',
            harga: 0,
            tanggal_pemasangan: new Date().toISOString().substr(0, 10),
            km_pemasangan: 0
        }
      },
      errors: {}
    }
  },
  computed: {
    ...mapState('kategoriKomponen', ['kategoriList']),
    selectedCategoryName() {
        const cat = this.kategoriList.find(c => c.id === this.form.kategori_komponen_id);
        return cat ? cat.nama_kategori : '';
    },
    isBanCategory() {
        return this.selectedCategoryName.toLowerCase().includes('ban');
    }
  },
  watch: {
    isOpen(v) {
      if (v) {
        this.fetchKategori()
        this.populateForm()
      }
    }
  },
  methods: {
    ...mapActions('kategoriKomponen', ['fetchKategori']),
    populateForm() {
        if (!this.componentData) return;
        const c = this.componentData;
        
        let target_tanggal = c.target_tanggal;
        if (target_tanggal) {
            try { target_tanggal = target_tanggal.split('T')[0] } catch(e){}
        }

        this.form = {
            kategori_komponen_id: c.kategori_komponen_id,
            nama_komponen: c.nama_komponen,
            tipe_pelacakan: c.tipe_pelacakan,
            target_km: c.target_km || '',
            target_hari: c.target_hari || '',
            target_tanggal: target_tanggal || '',
            has_identity: !!(c.has_identity || (c.has_identity === 1)),
            detail: {
                nomor_seri: '',
                nomor_stamp: '',
                jenis_ban: 'ORI',
                ukuran: '',
                merk_tipe: '',
                pemasok: '',
                harga: 0,
                tanggal_pemasangan: new Date().toISOString().substr(0, 10),
                km_pemasangan: 0
            }
        }
        
        if (this.form.has_identity && c.detail && c.detail.length > 0) {
            const detail = c.detail[0];
            let tanggal_pasang = detail.tanggal_pemasangan;
            if (tanggal_pasang) {
                try { tanggal_pasang = tanggal_pasang.split('T')[0] } catch(e){}
            }
            this.form.detail = {
                nomor_seri: detail.nomor_seri || '',
                nomor_stamp: detail.nomor_stamp || '',
                jenis_ban: detail.jenis_ban || 'ORI',
                ukuran: detail.ukuran || '',
                merk_tipe: detail.merk_tipe || '',
                pemasok: detail.pemasok || '',
                harga: detail.harga || 0,
                tanggal_pemasangan: tanggal_pasang || new Date().toISOString().substr(0, 10),
                km_pemasangan: detail.km_pemasangan || 0
            }
        }
        
        this.errors = {}
    },
    validate() {
      const e = {}
      if (!this.form.kategori_komponen_id) e.kategori_komponen_id = 'Kategori wajib dipilih'
      if (!this.form.nama_komponen) e.nama_komponen = 'Nama komponen wajib diisi'
      if (this.form.tipe_pelacakan === 'km' && !this.form.target_km) e.target_km = 'Target KM wajib diisi'
      if (this.form.tipe_pelacakan === 'days' && !this.form.target_hari) e.target_hari = 'Durasi wajib diisi'
      if (this.form.tipe_pelacakan === 'date' && !this.form.target_tanggal) e.target_tanggal = 'Tanggal wajib diisi'
      
      this.errors = e
      return Object.keys(e).length === 0
    },
    submit() {
      if (!this.validate()) return
      this.loading = true
      
      const payload = {
        ...this.form,
        target_km: this.form.tipe_pelacakan === 'km' ? parseInt(this.form.target_km) : null,
        target_hari: this.form.tipe_pelacakan === 'days' ? parseInt(this.form.target_hari) : null,
        target_tanggal: this.form.tipe_pelacakan === 'date' ? this.form.target_tanggal : null,
      }

      // If no identity, remove detail
      if (!this.form.has_identity) {
          delete payload.detail;
      }

      componentService.updateComponent(this.componentData.id, payload)
        .then(res => { 
          if (res.data.status === 'success' || res.data.success) { 
            this.$emit('component-updated'); 
            this.$emit('close') 
          } 
        })
        .catch(err => { 
            console.error(err);
            alert(err.response?.data?.message || 'Gagal mengubah komponen') 
        })
        .finally(() => { this.loading = false })
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.modal-overlay { position: fixed; inset: 0; background: rgba(15,15,40,0.55); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.18); font-family: 'Poppins', sans-serif; }

.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f0f0f8; position: sticky; top: 0; background: #fff; z-index: 10; }
.modal-header-left { display: flex; align-items: center; gap: 0.7rem; }
.modal-icon { width: 38px; height: 38px; background: #e0e7ff; color: #4338ca; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-title { font-size: 0.92rem; font-weight: 600; color: #1e1d4c; }
.modal-sub { font-size: 0.73rem; color: #9ca3af; margin-top: 1px; }
.close-btn { background: #f5f5fb; border: none; border-radius: 8px; color: #9ca3af; cursor: pointer; padding: 7px; display: flex; align-items: center; transition: all 0.15s; }
.close-btn:hover { background: #f0f0f0; color: #374151; }

.modal-body { padding: 1.1rem 1.4rem; display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
label { font-size: 0.8rem; font-weight: 600; color: #3E3D90; text-transform: uppercase; letter-spacing: 0.3px; }
.req { color: #ef4444; font-weight: 700; }

input[type="text"], input[type="number"], input[type="date"], select { padding: 0.65rem 0.9rem; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.875rem; outline: none; background: #fff; color: #374151; box-sizing: border-box; width: 100%; transition: border-color 0.2s, box-shadow 0.2s; }
input:focus, select:focus { border-color: #3E3D90; box-shadow: 0 0 0 3px rgba(62,61,144,0.1); }
input::placeholder { color: #c0c0d0; }
.input-error { border-color: #ef4444 !important; }

.hint { font-size: 0.72rem; color: #b0b0c8; }
.field-error { font-size: 0.72rem; color: #ef4444; font-weight: 500; }

.radio-group { display: flex; gap: 0.5rem; }
.radio-label { display: flex; align-items: center; gap: 5px; font-size: 0.78rem; color: #6b7280; cursor: pointer; padding: 0.45rem 0.6rem; border: 1.5px solid #e8e8f0; border-radius: 9px; transition: all 0.15s; flex: 1; justify-content: center; }
.radio-label input[type="radio"] { display: none; }
.radio-label.active { border-color: #3E3D90; background: #f0f0fb; color: #3E3D90; font-weight: 600; }

/* Identity Toggle */
.identity-section { border-top: 1px solid #f0f0f8; padding-top: 1rem; margin-top: 0.5rem; }
.toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.toggle-label { display: flex; flex-direction: column; cursor: pointer; }
.toggle-title { font-size: 0.85rem; font-weight: 600; color: #1e1d4c; }
.toggle-sub { font-size: 0.72rem; color: #9ca3af; }

.switch { width: 44px; height: 22px; background: #e2e8f0; border-radius: 99px; position: relative; cursor: pointer; transition: background 0.2s; }
.switch.active { background: #3E3D90; }
.switch-handle { width: 18px; height: 18px; background: #fff; border-radius: 50%; position: absolute; left: 2px; top: 2px; transition: left 0.2s; }
.switch.active .switch-handle { left: 24px; }

.identity-form-box { margin-top: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; }
.identity-header { display: flex; align-items: center; gap: 7px; font-size: 0.78rem; font-weight: 600; color: #3E3D90; margin-bottom: 1rem; text-transform: uppercase; }
.grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

@media (max-width: 480px) {
  .grid-form { grid-template-columns: 1fr; }
}

.fade-in { animation: fadeIn 0.3s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.3s ease; }
.slide-fade-enter, .slide-fade-leave-to { opacity: 0; transform: translateY(-10px); }

.modal-footer { display: flex; justify-content: flex-end; gap: 0.65rem; padding: 1rem 1.4rem; border-top: 1px solid #f0f0f8; position: sticky; bottom: 0; background: #fff; z-index: 10; }
.btn-cancel { padding: 0.6rem 1.1rem; background: #fff; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.15s; }
.btn-submit { display: flex; align-items: center; gap: 6px; padding: 0.6rem 1.1rem; background: #3E3D90; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 600; color: #fff; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: all 0.15s; }
.btn-submit:hover:not(:disabled) { background: #4c4bb0; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
</style>