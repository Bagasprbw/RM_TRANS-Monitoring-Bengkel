<template>
  <div class="page-layout">
    <Sidebar />
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h2 class="page-title">Daftar Kendaraan</h2>
          <p class="page-sub">Kelola data kendaraan workshop</p>
        </div>
        <button class="btn-primary" @click="showAdd = true" :disabled="loading">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          {{ loading ? 'Memuat...' : 'Tambah Kendaraan' }}
        </button>
      </header>

      <div class="content-body">
        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card stat-total">
            <div class="stat-icon-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 17h14v-5H5v5zM5 12l2-7h10l2 7" />
                <circle cx="7.5" cy="17" r="2" />
                <circle cx="16.5" cy="17" r="2" />
              </svg>
            </div>
            <div>
              <p class="stat-label">Total Kendaraan</p>
              <p class="stat-value">{{ armadaList.length }}</p>
            </div>
          </div>
          <div class="stat-card" v-for="m in dynamicMerkList" :key="m.id">
            <div class="stat-icon-wrap" :style="{ background: m.bg, color: m.color }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 17h14v-5H5v5zM5 12l2-7h10l2 7" />
                <circle cx="7.5" cy="17" r="2" />
                <circle cx="16.5" cy="17" r="2" />
              </svg>
            </div>
            <div>
              <p class="stat-label">{{ m.name }}</p>
              <p class="stat-value">{{ getMerkCount(m.id) }}</p>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="filters-row">
          <div class="search-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="15" height="15"
              stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Cari nomor polisi, merk, atau jenis..." />
            <button v-if="searchQuery" class="clear-btn" @click="searchQuery = ''">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="13" height="13" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>
          <select v-model="selectedMerk" class="select-filter">
            <option value="">Semua Merk</option>
            <option v-for="m in merkArmadaList" :key="m.id" :value="m.id">{{ m.nama_merk }}</option>
          </select>
          <button class="btn-outline" @click="showManageMaster = true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
              <path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
            </svg>
            Kelola Merk & Jenis
          </button>
        </div>

        <!-- Table -->
        <div class="card">
          <div class="card-header">
            <div class="card-header-left">
              <span class="card-title">Daftar Kendaraan</span>
              <span class="card-badge">{{ filteredList.length }}</span>
            </div>
            <button v-if="!loading" class="refresh-btn" @click="refreshData" title="Refresh">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 4v6h-6M1 20v-6h6" />
                <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" />
              </svg>
            </button>
          </div>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nomor Polisi</th>
                  <th>Merk | Jenis</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredList.length === 0 && !loading">
                  <td colspan="5" class="empty-row">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="1.5">
                      <circle cx="11" cy="11" r="8" />
                      <path d="M21 21l-4.35-4.35" />
                    </svg>
                    <p>Tidak ada data yang cocok</p>
                  </td>
                </tr>
                <tr v-if="loading && filteredList.length === 0">
                  <td colspan="5" class="empty-row">
                    <div class="spinner-small"></div>
                    <p>Memuat data...</p>
                  </td>
                </tr>
                <tr v-for="(k, index) in filteredList" :key="k.id">
                  <td class="text-muted">{{ index + 1 }}</td>
                  <td>
                    <div class="nopol-cell">
                      <span class="nopol-text">{{ k.nopol }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="type-badge-wrap">
                      <span class="badge badge-merk" :style="getMerkBadgeStyle(k.merk?.id)">
                        {{ k.merk?.nama_merk || '-' }}
                      </span>
                      <span class="type-connector">|</span>
                      <span class="type-text">{{ k.jenis?.nama_jenis || '-' }}</span>
                    </div>
                  </td>
                  <td>
                    <span 
                      v-if="k.monitoring && k.monitoring.status === 'aktif'" 
                      class="badge badge-green"
                    >Aktif (Di-Monitor)</span>
                    <span 
                      v-else-if="k.monitoring && k.monitoring.status === 'nonaktif'" 
                      class="badge badge-gray"
                    >Non &nbsp;  Aktif</span>
                    <span 
                      v-else 
                      class="badge badge-yellow"
                    >Belum Di-Monitor</span>
                  </td>
                  <td>
                    <div class="action-btns">
                      <button class="icon-btn edit" @click="openEdit(k)" title="Edit" :disabled="loading">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="15" height="15"
                          stroke-width="2">
                          <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                          <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </button>
                      <button class="icon-btn delete" @click="deleteItem(k.id)" title="Hapus" :disabled="loading">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="15" height="15"
                          stroke-width="2">
                          <polyline points="3,6 5,6 21,6" />
                          <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
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

    <AddKendaraanModal :is-open="showAdd" @close="showAdd = false" @kendaraan-added="addKendaraan" />
    <EditKendaraanModal :is-open="showEdit" :kendaraan="selectedKendaraan" @close="closeEdit"
      @kendaraan-updated="updateKendaraan" />
    <ManageMasterModal :is-open="showManageMaster" @close="showManageMaster = false" />
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import Sidebar from '@/components/Sidebar.vue'
import AddKendaraanModal from '@/components/AddKendaraanModal.vue'
import EditKendaraanModal from '@/components/EditKendaraanModal.vue'
import ManageMasterModal from '@/components/ManageMasterModal.vue'

export default {
  name: 'KendaraanView',
  components: { Sidebar, AddKendaraanModal, EditKendaraanModal, ManageMasterModal },
  data() {
    return {
      searchQuery: '',
      selectedMerk: '',
      showAdd: false,
      showEdit: false,
      showManageMaster: false,
      selectedKendaraan: null
    }
  },
  computed: {
    ...mapState('armada', ['loading']),
    ...mapGetters('armada', ['armadaList', 'jenisArmadaList', 'merkArmadaList']),

    dynamicMerkList() {
      const colors = [
        { bg: '#ede9fe', color: '#6d28d9' },  // Purple
        { bg: '#dbeafe', color: '#1d4ed8' }, // Blue
        { bg: '#fee2e2', color: '#991b1b' }, // Red
        { bg: '#fef9c3', color: '#854d0e' }, // Yellow
        { bg: '#dcfce7', color: '#15803d' }, // Green
      ]
      return this.merkArmadaList.map((m, index) => ({
        id: m.id,
        name: m.nama_merk,
        ...colors[index % colors.length]
      }))
    },

    filteredList() {
      let list = this.armadaList
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase()
        list = list.filter(k =>
          k.nopol.toLowerCase().includes(q) ||
          (k.merk?.nama_merk || '').toLowerCase().includes(q) ||
          (k.jenis?.nama_jenis || '').toLowerCase().includes(q)
        )
      }
      if (this.selectedMerk) {
        list = list.filter(k => k.merk_armada_id === this.selectedMerk)
      }
      return list
    }
  },
  mounted() {
    this.loadArmadaData()
  },
  methods: {
    ...mapActions('armada', [
      'fetchArmada',
      'fetchJenisArmada',
      'fetchMerkArmada',
      'createArmada',
      'updateArmada',
      'deleteArmada'
    ]),

    async loadArmadaData() {
      await Promise.all([
        this.fetchArmada(),
        this.fetchJenisArmada(),
        this.fetchMerkArmada()
      ])
    },

    async refreshData() {
      await this.loadArmadaData()
    },

    getMerkCount(merkId) {
      return this.armadaList.filter(k => k.merk_armada_id === merkId).length
    },

    async addKendaraan(data) {
      const result = await this.createArmada(data)
      if (result.success) {
        this.showAdd = false
      }
    },

    openEdit(k) {
      this.selectedKendaraan = {
        id: k.id,
        nopol: k.nopol,
        merk_armada_id: k.merk_armada_id,
        jenis_armada_id: k.jenis_armada_id
      }
      this.showEdit = true
    },

    closeEdit() {
      this.showEdit = false
      this.selectedKendaraan = null
    },

    async updateKendaraan(data) {
      const result = await this.updateArmada({
        id: this.selectedKendaraan.id,
        data: {
          nopol: data.nopol,
          merk_armada_id: data.merk_armada_id,
          jenis_armada_id: data.jenis_armada_id
        }
      })

      if (result.success) {
        this.closeEdit()
      }
    },

    async deleteItem(id) {
      await this.deleteArmada(id)
    },

    getMerkBadgeStyle(merkId) {
      const merkData = this.dynamicMerkList.find(m => m.id === merkId)
      if (merkData) {
        return { backgroundColor: merkData.bg, color: merkData.color }
      }
      return { backgroundColor: '#f3f4f6', color: '#374151' }
    }
  }
}
</script>

<style scoped>
/* ===== LAYOUT ===== */
.page-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

.main-content {
  flex: 1;
  background: #f0f0f8;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

/* ===== TOPBAR ===== */
.topbar {
  background: #fff;
  border-bottom: 1px solid #e8e8f0;
  min-height: 80px;
  box-sizing: border-box;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 10;
}

.page-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e1d4c;
}

.page-sub {
  font-size: 0.78rem;
  color: #9ca3af;
  margin-top: 2px;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 0.6rem 1.2rem;
  background: #3E3D90;
  border: none;
  border-radius: 10px;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  font-size: 0.83rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
  box-shadow: 0 4px 12px rgba(62, 61, 144, 0.3);
}

.btn-primary:hover {
  background: #4c4bb0;
  transform: translateY(-1px);
}

/* ===== CONTENT ===== */
.content-body {
  flex: 1;
  padding: 1.75rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* ===== STATS ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-card {
  background: #fff;
  border-radius: 14px;
  padding: 1.1rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.85rem;
  border: 1px solid #e8e8f0;
  transition: transform 0.15s, box-shadow 0.15s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(62, 61, 144, 0.09);
}

.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #f0f0f8;
  color: #3E3D90;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-total .stat-icon-wrap {
  background: #ede9fe;
  color: #6d28d9;
}

.stat-label {
  font-size: 0.72rem;
  font-weight: 500;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.stat-value {
  font-size: 1.7rem;
  font-weight: 700;
  color: #1e1d4c;
  line-height: 1.1;
}

/* ===== FILTERS ===== */
.filters-row {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.search-wrap {
  position: relative;
  flex: 1;
  max-width: 420px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  pointer-events: none;
}

.search-wrap input {
  width: 100%;
  padding: 0.65rem 2.2rem 0.65rem 36px;
  border: 1.5px solid #e8e8f0;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.83rem;
  outline: none;
  background: #fff;
  color: #374151;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.search-wrap input:focus {
  border-color: #3E3D90;
  box-shadow: 0 0 0 3px rgba(62, 61, 144, 0.1);
}

.search-wrap input::placeholder {
  color: #c0c0d0;
}

.clear-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 2px;
  display: flex;
}

.clear-btn:hover {
  color: #374151;
}

.select-filter {
  padding: 0.65rem 1rem;
  border: 1.5px solid #e8e8f0;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.83rem;
  outline: none;
  background: #fff;
  color: #374151;
  cursor: pointer;
  transition: border-color 0.2s;
}

.select-filter:focus {
  border-color: #3E3D90;
}

/* ===== CARD ===== */
.card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e8e8f0;
  overflow: hidden;
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f0f0f8;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-header-left {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e1d4c;
}

.card-badge {
  padding: 0.2rem 0.65rem;
  background: #f0f0f8;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  color: #6b7280;
}

/* ===== TABLE ===== */
.table-wrap {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead th {
  padding: 0.7rem 1.25rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: #fafafa;
  text-align: left;
  border-bottom: 1px solid #f0f0f8;
}

.table tbody td {
  padding: 0.9rem 1.25rem;
  font-size: 0.85rem;
  color: #374151;
  border-bottom: 1px solid #f5f5fb;
  vertical-align: middle;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.table tbody tr:hover td {
  background: #faf9ff;
}

.text-muted {
  color: #b0b0c8;
  font-size: 0.8rem;
}

/* ===== NOPOL ===== */
.nopol-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nopol-text {
  font-weight: 700;
  color: #1e1d4c;
  letter-spacing: 0.3px;
}

.type-badge-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.badge-merk {
  min-width: 60px;
  justify-content: center;
}

.type-connector {
  color: #d1d5db;
  font-weight: 300;
}

.type-text {
  font-weight: 500;
  color: #6b7280;
  font-size: 0.8rem;
}

.btn-outline {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 0.65rem 1rem;
  background: #fff;
  border: 1.5px solid #e8e8f0;
  border-radius: 10px;
  color: #3E3D90;
  font-family: 'Poppins', sans-serif;
  font-size: 0.83rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-outline:hover {
  background: #f0f0fb;
  border-color: #3E3D90;
}

/* ===== EMPTY ===== */
.empty-row {
  text-align: center;
  padding: 3rem !important;
  color: #9ca3af;
}

.empty-row svg {
  margin: 0 auto 0.5rem;
  display: block;
}

.empty-row p {
  font-size: 0.85rem;
}

/* ===== ACTIONS ===== */
.action-btns {
  display: flex;
  gap: 5px;
}

.icon-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: none;
  border-radius: 8px;
  cursor: pointer;
  color: #b0b0c8;
  transition: all 0.15s;
}

.icon-btn.edit:hover {
  color: #3E3D90;
  background: #f0f0fb;
}

.icon-btn.delete:hover {
  color: #ef4444;
  background: #fff0f0;
}

/* ===== BADGES ===== */
.badge {
  display: inline-flex;
  padding: 0.25rem 0.7rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
}

.badge-blue {
  background: #dbeafe;
  color: #1d4ed8;
}

.badge-red {
  background: #fee2e2;
  color: #991b1b;
}

.badge-yellow {
  background: #fef9c3;
  color: #854d0e;
}

.badge-green {
  background: #dcfce7;
  color: #15803d;
}

.badge-purple {
  background: #ede9fe;
  color: #6d28d9;
}

.badge-gray {
  background: #f3f4f6;
  color: #374151;
}

/* ===== TOGGLE SWITCH ===== */
.switch { position: relative; display: inline-block; width: 36px; height: 20px; vertical-align: middle; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e2e8f0; transition: .3s; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
input:checked + .slider { background-color: #22c55e; }
input:checked + .slider:before { transform: translateX(16px); }
.slider.round { border-radius: 20px; }
.slider.round:before { border-radius: 50%; }

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .topbar {
    padding: 1rem 1rem 1rem 4rem;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .page-title {
    font-size: 1rem;
  }

  .content-body {
    padding: 1rem;
    gap: 1rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .filters-row {
    flex-wrap: wrap;
  }

  .search-wrap {
    max-width: 100%;
    flex: 1 1 200px;
  }

  .select-filter {
    flex: 1 1 auto;
  }

  /* Hide less important table columns */
  .table thead th:nth-child(1),
  .table tbody td:nth-child(1),
  .table thead th:nth-child(4),
  .table tbody td:nth-child(4) {
    display: none;
  }

  .table thead th,
  .table tbody td {
    padding: 0.75rem 1rem;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .topbar {
    padding: 0.85rem 0.85rem 0.85rem 3.5rem;
  }

  .btn-primary {
    padding: 0.5rem 0.85rem;
    font-size: 0.78rem;
  }

  .btn-primary svg {
    display: none;
  }

  .card-header {
    padding: 0.85rem 1rem;
  }

  .table tbody td {
    padding: 0.65rem 0.75rem;
    font-size: 0.8rem;
  }
}

/* Style yang sudah ada sebelumnya, ditambah: */
.refresh-btn {
  background: none;
  border: none;
  padding: 6px;
  border-radius: 8px;
  cursor: pointer;
  color: #9ca3af;
  transition: all 0.15s;
}

.refresh-btn:hover {
  background: #f0f0f8;
  color: #3E3D90;
}

.spinner-small {
  width: 24px;
  height: 24px;
  border: 2px solid #e8e8f0;
  border-top-color: #3E3D90;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  margin: 0 auto 0.5rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Style lainnya tetap sama seperti sebelumnya */
</style>