<template>
  <div class="page-layout">
    <Sidebar />
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h2 class="page-title">Monitoring Kendaraan</h2>
          <p class="page-sub"><span class="accent">{{ monitoringList.length }}</span> kendaraan aktif</p>
        </div>
        <div class="topbar-actions">
          <div ref="importDropdown" class="import-dropdown-wrap">
            <button class="btn-import" @click="showImportMenu = !showImportMenu">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              <span>Import Excel</span>
              <svg class="chevron-icon" :class="{ open: showImportMenu }" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="12" height="12" stroke-width="2.5">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </button>
            <div v-if="showImportMenu" class="import-dropdown-menu">
              <button class="dropdown-item" @click="openImport('monitoring')">
                <div class="dropdown-icon icon-cyan">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="dropdown-text">
                  <div class="dropdown-title">Aktivasi Monitoring</div>
                  <div class="dropdown-desc">Daftarkan kendaraan ke monitoring</div>
                </div>
              </button>
              <button class="dropdown-item" @click="openImport('logkm')">
                <div class="dropdown-icon icon-green">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="dropdown-text">
                  <div class="dropdown-title">Log KM (Odometer)</div>
                  <div class="dropdown-desc">Update kilometer harian massal</div>
                </div>
              </button>
            </div>
          </div>
          <button class="btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download Report
          </button>
          <button class="btn-primary" @click="showAdd = true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Kendaraan
          </button>
        </div>
      </header>

      <div class="content-body">
        <div class="card">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <div>
                <span class="card-title">Daftar Monitoring</span>
                <span class="card-badge">{{ filteredList.length }}</span>
              </div>
            </div>
            <div class="search-wrap">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
              </svg>
              <input v-model="searchQuery" type="text" placeholder="Cari plat nomor atau jenis..." />
              <button v-if="searchQuery" class="clear-btn" @click="searchQuery = ''">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="12" height="12" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
          </div>

          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Plat Nomor</th>
                  <th>Merk | Jenis</th>
                  <th>Total KM</th>
                  <th>Kondisi</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredList.length === 0">
                  <td colspan="6" class="empty-row">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <p>Tidak ada kendaraan ditemukan</p>
                  </td>
                </tr>
                <tr
                  v-else
                  v-for="item in filteredList"
                  :key="item.id"
                  @click="item.status === 'aktif' ? $router.push('/monitoring-kendaraan/' + item.id) : null"
                  class="clickable"
                  :class="{ 'row-inactive': item.status !== 'aktif' }"
                >
                  <td>
                    <div class="nopol-cell">
                      <span class="nopol-text">{{ getPlatNomor(item) }}</span>
                      <span v-if="getCriticalCount(item) > 0" class="critical-badge">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2z"/></svg>
                        {{ getCriticalCount(item) }} kritis
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="merk-jenis-wrap">
                      <span class="merk-name">{{ item.armada?.merk?.nama_merk || '-' }}</span>
                      <span class="sep">|</span>
                      <span class="jenis-name">{{ item.armada?.jenis?.nama_jenis || '-' }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="km-text">{{ formatNumber(item.last_recorded_km) }} <span class="km-unit">km</span></span>
                  </td>
                  <td>
                    <span class="status-dot" :class="item.status === 'aktif' ? (getCriticalCount(item) > 0 ? 'dot-warn' : 'dot-ok') : 'dot-inactive'"></span>
                    <span class="status-text" :class="item.status === 'aktif' ? (getCriticalCount(item) > 0 ? 'text-warn' : 'text-ok') : 'text-inactive'">
                      {{ item.status === 'aktif' ? (getCriticalCount(item) > 0 ? 'Perlu Perhatian' : 'Aktif') : 'Non-aktif' }}
                    </span>
                  </td>
                  <td @click.stop>
                    <label class="switch" :title="item.status === 'aktif' ? 'Klik untuk Non-aktifkan' : 'Klik untuk Aktifkan'">
                      <input type="checkbox" :checked="item.status === 'aktif'" @change="toggleStatus(item)">
                      <span class="slider round"></span>
                    </label>
                  </td>
                  <td @click.stop>
                    <div class="action-wrap">
                      <button 
                        v-if="item.status === 'aktif'"
                        class="icon-btn detail" 
                        @click="$router.push('/monitoring-kendaraan/' + item.id)" 
                        :title="`Lihat kesehatan komponen untuk armada ${getPlatNomor(item)}`"
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                      </button>
                      <button class="icon-btn delete" @click="removeVehicle(item.id)" title="Hapus dari Monitoring">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19,6V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <AddVehicleToMonitoringModal
      :is-open="showAdd"
      @close="showAdd = false"
      @vehicle-added="handleVehicleAdded"
    />
    <ImportModal :is-open="showImport" :module="importModule" @close="showImport = false" @imported="onMonitoringImported" />
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar.vue'
import AddVehicleToMonitoringModal from '@/components/AddVehicleToMonitoringModal.vue'
import ImportModal from '@/components/ImportModal.vue'
import { mapState, mapActions, mapGetters } from 'vuex'
import Swal from 'sweetalert2'

export default {
  name: 'MonitoringKendaraanView',
  components: { Sidebar, AddVehicleToMonitoringModal, ImportModal },
  data() {
    return {
      searchQuery: '',
      showAdd: false,
      showImport: false,
      importModule: 'monitoring',
      showImportMenu: false
    }
  },
  computed: {
    ...mapState('monitoring', ['monitoringList', 'loading', 'reminders']),
    filteredList() {
      if (!this.monitoringList) return []
      let list = this.monitoringList
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase()
        list = list.filter(i => {
          const nopol = i.armada?.nopol || i.plat_nomor || ''
          const jenis = i.armada?.jenis?.nama_jenis || i.jenis_kendaraan || ''
          const merk = i.armada?.merk?.nama_merk || ''
          return nopol.toLowerCase().includes(q) || 
                 jenis.toLowerCase().includes(q) || 
                 merk.toLowerCase().includes(q)
        })
      }
      
      // Sort: aktif first, nonaktif last
      return [...list].sort((a, b) => {
        if (a.status === 'aktif' && b.status !== 'aktif') return -1
        if (a.status !== 'aktif' && b.status === 'aktif') return 1
        return 0
      })
    }
  },
  mounted() {
    this.fetchMonitoring()
    this.fetchReminders()
    document.addEventListener('click', this.handleOutsideClick)
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleOutsideClick)
  },
  methods: {
    ...mapActions('monitoring', ['fetchMonitoring', 'deleteMonitoring', 'updateStatus', 'fetchReminders']),
    async handleVehicleAdded() { 
      this.showAdd = false
    },
    handleOutsideClick(e) {
      if (this.$refs.importDropdown && !this.$refs.importDropdown.contains(e.target)) {
        this.showImportMenu = false
      }
    },
    openImport(module) {
      this.importModule = module
      this.showImportMenu = false
      this.showImport = true
    },
    async onMonitoringImported() {
      await this.fetchMonitoring()
    },
    async removeVehicle(id) {
      await this.deleteMonitoring(id)
    },
    async toggleStatus(item) {
      const isAktif = item.status === 'aktif'
      const newStatus = isAktif ? 'nonaktif' : 'aktif'
      
      const result = await Swal.fire({
        title: isAktif ? 'Matikan Monitoring?' : 'Aktifkan Monitoring?',
        text: isAktif 
          ? 'Kendaraan ini tidak akan muncul di dashboard utama.' 
          : 'Kendaraan ini akan kembali muncul di dashboard utama.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3E3D90',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: isAktif ? 'Ya, Matikan' : 'Ya, Aktifkan',
        cancelButtonText: 'Batal'
      })

      if (!result.isConfirmed) return

      const originalStatus = item.status
      item.status = newStatus // optimistic

      try {
        const res = await this.updateStatus({
          id: item.id,
          data: { status: newStatus }
        })
        
        if (res.success) {
          Swal.fire({
            title: 'Berhasil',
            text: `Monitoring telah ${newStatus === 'aktif' ? 'diaktifkan' : 'dimatikan'}.`,
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          })
        } else {
          throw new Error(res.error)
        }
      } catch (e) {
        item.status = originalStatus
        Swal.fire('Gagal', 'Gagal mengubah status monitoring.', 'error')
      }
    },
    formatNumber(n) { return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') },
    
    getPlatNomor(item) {
      return item.armada?.nopol || item.plat_nomor || 'Unknown'
    },
    getJenisKendaraan(item) {
      return item.armada?.jenis?.nama_jenis || item.jenis_kendaraan || 'Unknown'
    },
    getCriticalCount(item) {
      // Hitung jumlah komponen kritis dari reminders Vuex berdasarkan monitoring_id
      return this.reminders.filter(r => r.monitoring_id === item.id).length
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
</style>

<style scoped>
/* ===== TOGGLE SWITCH ===== */
.switch { position: relative; display: inline-block; width: 36px; height: 20px; vertical-align: middle; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e2e8f0; transition: .3s; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
input:checked + .slider { background-color: #22c55e; }
input:checked + .slider:before { transform: translateX(16px); }
.slider.round { border-radius: 20px; }
.slider.round:before { border-radius: 50%; }
.page-layout { display: flex; min-height: 100vh; font-family: 'Poppins', sans-serif; }
.main-content { flex: 1; background: #f0f0f8; display: flex; flex-direction: column; min-width: 0; }

.topbar { background: #fff; border-bottom: 1px solid #e8e8f0; min-height: 80px; box-sizing: border-box; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10; }
.page-title { font-size: 1.2rem; font-weight: 700; color: #1e1d4c; }
.page-sub { font-size: 0.78rem; color: #9ca3af; margin-top: 2px; }
.accent { color: #3E3D90; font-weight: 600; }
.topbar-actions { display: flex; gap: 0.65rem; }

.btn-primary { display: flex; align-items: center; gap: 6px; padding: 0.55rem 1.1rem; background: #3E3D90; border: none; border-radius: 10px; color: #fff; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: background 0.15s, transform 0.1s; }
.btn-primary:hover { background: #4c4bb0; transform: translateY(-1px); }
.btn-secondary { display: flex; align-items: center; gap: 6px; padding: 0.55rem 1rem; background: #fff; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 500; color: #374151; cursor: pointer; transition: all 0.15s; }
.btn-secondary:hover { border-color: #3E3D90; color: #3E3D90; background: #f5f5fb; }
.btn-import { display: flex; align-items: center; gap: 6px; padding: 0.55rem 1rem; background: #fff; border: 1.5px solid #6366f1; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; font-weight: 600; color: #4f46e5; cursor: pointer; transition: all 0.15s; }
.btn-import:hover { background: #eef2ff; transform: translateY(-1px); }

.import-dropdown-wrap { position: relative; }
.chevron-icon { transition: transform 0.2s; margin-left: 2px; }
.chevron-icon.open { transform: rotate(180deg); }
.import-dropdown-menu { position: absolute; top: calc(100% + 6px); left: 0; background: #fff; border-radius: 12px; border: 1px solid #e8e8f0; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); padding: 6px; min-width: 250px; z-index: 100; display: flex; flex-direction: column; gap: 4px; }
.dropdown-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; border: none; background: transparent; cursor: pointer; text-align: left; transition: background 0.15s; width: 100%; font-family: 'Poppins', sans-serif; }
.dropdown-item:hover { background: #f8fafc; }
.dropdown-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.dropdown-icon.icon-cyan { background: #ecfeff; color: #0891b2; }
.dropdown-icon.icon-green { background: #ecfdf5; color: #059669; }
.dropdown-title { font-size: 0.82rem; font-weight: 600; color: #1e1d4c; line-height: 1.2; }
.dropdown-desc { font-size: 0.72rem; color: #9ca3af; margin-top: 2px; }

.content-body { flex: 1; padding: 1.75rem 2rem; }

.card { background: #fff; border-radius: 14px; border: 1px solid #e8e8f0; overflow: hidden; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f8; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.card-header-left { display: flex; align-items: center; gap: 0.7rem; }
.card-icon { width: 34px; height: 34px; background: #ede9fe; color: #6d28d9; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card-title { font-size: 0.92rem; font-weight: 600; color: #1e1d4c; margin-right: 0.5rem; }
.card-badge { padding: 0.2rem 0.65rem; background: #f0f0f8; border-radius: 999px; font-size: 0.72rem; font-weight: 600; color: #6b7280; }

.search-wrap { position: relative; }
.search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.search-wrap input { padding: 0.6rem 2rem 0.6rem 33px; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; outline: none; background: #fff; color: #374151; width: 260px; transition: border-color 0.2s, box-shadow 0.2s; }
.search-wrap input:focus { border-color: #3E3D90; box-shadow: 0 0 0 3px rgba(62,61,144,0.1); }
.search-wrap input::placeholder { color: #c0c0d0; }
.clear-btn { position: absolute; right: 9px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 2px; display: flex; }

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table thead th { padding: 0.7rem 1.25rem; font-size: 0.7rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; background: #fafafa; text-align: left; border-bottom: 1px solid #f0f0f8; }
.table tbody td { padding: 0.95rem 1.25rem; font-size: 0.85rem; color: #374151; border-bottom: 1px solid #f5f5fb; vertical-align: middle; }
.table tbody tr:last-child td { border-bottom: none; }
.clickable { cursor: pointer; }
.clickable:hover td { background: #f0f0ff; }

.nopol-cell { display: flex; align-items: center; gap: 9px; }
.nopol-text { font-weight: 700; color: #1e1d4c; letter-spacing: 0.3px; }
.critical-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; background: #fee2e2; color: #991b1b; border-radius: 999px; font-size: 0.68rem; font-weight: 700; }

.merk-jenis-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
}

.merk-name {
  font-weight: 700;
  color: #1e1d4c;
  background: #f0f0fb;
  padding: 2px 8px;
  border-radius: 6px;
}

.sep {
  color: #d1d5db;
}

.jenis-name {
  color: #6b7280;
  font-weight: 500;
}

.km-text { font-weight: 600; color: #1e1d4c; }
.km-unit { font-size: 0.75rem; font-weight: 400; color: #9ca3af; }

.status-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 7px; vertical-align: middle; }
.dot-ok { background: #22c55e; box-shadow: 0 0 5px rgba(34,197,94,0.5); }
.dot-warn { background: #ef4444; box-shadow: 0 0 5px rgba(239,68,68,0.5); }
.dot-inactive { background: #94a3b8; }
.status-text { font-size: 0.8rem; font-weight: 500; }
.text-ok { color: #16a34a; }
.text-warn { color: #dc2626; }
.text-inactive { color: #94a3b8; }

.row-inactive { background: #fcfcfd; }
.row-inactive td { color: #9ca3af; }
.row-inactive .nopol-text, .row-inactive .km-text { color: #94a3b8; }
.row-inactive .merk-chip { opacity: 0.5; background: #f1f5f9; }
.row-inactive.clickable { cursor: default; }
.row-inactive.clickable:hover td { background: #fcfcfd; }

.empty-row { text-align: center; padding: 3rem !important; color: #9ca3af; }
.empty-row svg { margin: 0 auto 0.5rem; display: block; }
.empty-row p { font-size: 0.85rem; }

.action-wrap { display: flex; gap: 4px; align-items: center; }
.icon-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; background: none; border-radius: 8px; cursor: pointer; color: #b0b0c8; transition: all 0.15s; }
.icon-btn.detail:hover { color: #fff; background: #3E3D90; transform: scale(1.1); }
.icon-btn.activate:hover { color: #fff; background: #22c55e; transform: scale(1.1); }
.icon-btn.delete:hover { color: #fff; background: #ef4444; transform: scale(1.1); }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .topbar {
    padding: 1rem 1rem 1rem 4rem;
    flex-wrap: wrap;
    gap: 0.75rem;
  }
  .topbar-actions { flex-wrap: wrap; }
  .page-title { font-size: 1rem; }
  .content-body { padding: 1rem; }
  .card-header { flex-direction: column; align-items: flex-start; }
  .search-wrap input { width: 100%; }
  /* Hide some table columns on tablet */
  .table thead th:nth-child(3),
  .table tbody td:nth-child(3) { display: none; }
  .table thead th,
  .table tbody td { padding: 0.75rem 1rem; }
  .btn-secondary { display: none; }
}

@media (max-width: 480px) {
  .topbar { padding: 0.85rem 0.85rem 0.85rem 3.5rem; }
  .page-title { font-size: 0.95rem; }
  /* On small phone, also hide status column */
  .table thead th:nth-child(4),
  .table tbody td:nth-child(4) { display: none; }
  .table tbody td { padding: 0.65rem 0.75rem; font-size: 0.8rem; }
  .btn-primary { padding: 0.5rem 0.85rem; font-size: 0.78rem; }
  .btn-primary svg { display: none; }
  .card-header { padding: 0.85rem 1rem; }
}
</style>