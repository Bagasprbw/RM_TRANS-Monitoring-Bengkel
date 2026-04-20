<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title">Add Vehicle to Monitoring</h3>
        <button class="close-btn" @click="$emit('close')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20" height="20" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Pilih Kendaraan</label>
          <select v-model="form.kendaraan_id">
            <option value="">-- Pilih Kendaraan --</option>
            <option v-for="k in availableArmadaList" :key="k.id" :value="k.id">
              {{ k.nopol }} - {{ k.merk?.nama_merk || '-' }} | {{ k.jenis?.nama_jenis || '-' }}
            </option>
          </select>
          <p v-if="errors.kendaraan_id" class="err-msg">{{ errors.kendaraan_id }}</p>
        </div>
        <div class="form-group">
          <label>Initial Kilometer</label>
          <input v-model.number="form.initial_km" type="number" min="0" placeholder="Contoh: 50000" />
          <p class="hint">Kilometer awal kendaraan saat ini</p>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-cancel" @click="$emit('close')" :disabled="isSubmitting">Batal</button>
        <button class="btn-submit" @click="submit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Menyimpan...' : 'Tambahkan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'AddVehicleToMonitoringModal',
  props: { isOpen: { type: Boolean, required: true } },
  data() {
    return {
      form: { kendaraan_id: '', initial_km: 0 },
      errors: {},
      isSubmitting: false
    }
  },
  computed: {
    ...mapState('monitoring', ['availableArmadaList'])
  },
  watch: {
    isOpen(v) { 
      if (v) { 
        this.form = { kendaraan_id: '', initial_km: 0 }; 
        this.errors = {};
        this.fetchAvailableArmada();
      } 
    }
  },
  methods: {
    ...mapActions('monitoring', ['fetchAvailableArmada', 'createMonitoring']),
    async submit() {
      if (!this.form.kendaraan_id) { 
        this.errors = { kendaraan_id: 'Kendaraan harus dipilih' }; 
        return; 
      }
      
      this.isSubmitting = true;
      const res = await this.createMonitoring({
        armada_id: this.form.kendaraan_id,
        last_recorded_km: this.form.initial_km
      });
      this.isSubmitting = false;

      if (res.success) {
        this.$emit('vehicle-added', res.data)
        this.$emit('close')
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 440px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
.modal-title { font-size: 1rem; font-weight: 600; color: #1f2937; }
.close-btn { background: none; border: none; color: #9ca3af; cursor: pointer; }
.modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
label { font-size: 0.85rem; font-weight: 500; color: #374151; }
input, select { padding: 0.6rem 0.9rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; outline: none; }
input:focus, select:focus { border-color: #4f46e5; }
.hint { font-size: 0.75rem; color: #9ca3af; }
.err-msg { font-size: 0.75rem; color: #ef4444; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; }
.btn-cancel { padding: 0.6rem 1.25rem; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; cursor: pointer; }
.btn-submit { padding: 0.6rem 1.25rem; background: #4f46e5; border: none; border-radius: 8px; color: #fff; font-size: 0.875rem; font-weight: 500; cursor: pointer; }
</style>
