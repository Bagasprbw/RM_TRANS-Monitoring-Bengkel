<template>
  <div class="page-layout">
    <Sidebar />
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h2 class="page-title">Riwayat Aktivitas Perawatan</h2>
          <p class="page-sub">Data pemeliharaan lengkap sesuai standar operasional PT RM Trans</p>
        </div>
      </header>

      <div class="content-body">
        <!-- Filters Card -->
        <div class="card filter-card">
          <div class="filter-row">
            <div class="filter-group">
              <label>Filter Kategori</label>
              <select v-model="filters.category_id" @change="fetchHistory(1)">
                <option value="">— Semua Kategori —</option>
                <option v-for="cat in kategoriList" :key="cat.id" :value="cat.id">
                  {{ cat.nama_kategori }}
                </option>
              </select>
            </div>
            <div class="filter-group search-group">
              <label>Cari Data</label>
              <div class="search-input-wrap">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input 
                  v-model="filters.search" 
                  type="text" 
                  placeholder="Cari nopol, komponen, no seri, atau catatan..." 
                  @keyup.enter="fetchHistory(1)"
                />
              </div>
            </div>
            <div class="filter-actions">
              <button class="btn-primary" @click="fetchHistory(1)" :disabled="loading">
                <svg v-if="!loading" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <span v-else class="loader-small"></span>
                Filter
              </button>
              <button class="btn-secondary" @click="resetFilters" :disabled="loading">Reset</button>
              <button class="btn-success" @click="handleExport" :disabled="loading || exporting">
                <svg v-if="!exporting" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span v-else class="loader-small"></span>
                Export Excel
              </button>
            </div>
          </div>
        </div>

        <!-- History Table Card -->
        <div class="card">
          <div class="table-header">
            <div class="table-header-left">
              <div class="table-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" stroke-width="2">
                  <path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/>
                </svg>
              </div>
              <span class="table-title">Riwayat Perawatan & Penggantian Terakhir</span>
            </div>
            <div v-if="pagination.total > 0" class="table-header-right">
              <div class="limit-control">
                <span>Tampilkan</span>
                <select v-model="filters.limit" @change="fetchHistory(1)">
                  <option :value="10">10</option>
                  <option :value="15">15</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
                <span>baris</span>
              </div>
              <div class="table-stats">
                {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} record
              </div>
            </div>
          </div>

          <div class="table-wrap">
            <table class="table" :class="{ 'table-loading': loading }">
              <thead>
                <tr>
                  <th class="text-center" width="50">NO</th>
                  <th>Tanggal</th>
                  <th>Kendaraan</th>
                  <th>Komponen & Kategori</th>
                  <th>Km Record</th>
                  <th>Detail Komponen (Identitas & Spesifikasi)</th>
                  <th>Catatan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="history.length === 0 && !loading">
                  <td colspan="7" class="empty-state">
                    <div class="empty-icon">
                      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                      </svg>
                    </div>
                    <p>Tidak ada riwayat perawatan ditemukan.</p>
                  </td>
                </tr>
                <tr v-for="(row, index) in history" :key="row.id">
                  <td class="text-center no-cell">
                    {{ calculateNo(index) }}
                  </td>
                  <td>
                    <div class="date-cell">
                      <span class="date-text">{{ formatDate(row.tanggal_selesai) }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="vehicle-cell">
                      <span class="nopol-text">{{ row.komponen?.monitoring?.armada?.nopol || '-' }}</span>
                      <span class="vehicle-type">{{ row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-' }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="comp-cell">
                      <span class="comp-name">{{ row.komponen?.nama_komponen }}</span>
                      <span v-if="!filters.category_id" class="category-badge">{{ row.komponen?.kategori?.nama_kategori }}</span>
                    </div>
                  </td>
                  <td class="km-cell">
                    {{ formatNumber(row.km_saat_selesai) }} <span class="unit">km</span>
                  </td>
                  
                  <!-- Detail Column: Rich Info -->
                  <td>
                    <div v-if="row.detail_komponen" class="history-detail-grid">
                      <!-- Ban Info -->
                      <template v-if="isBanType(row)">
                        <div class="detail-item"><span>No Seri:</span> <strong>{{ row.detail_komponen.nomor_seri || '-' }}</strong></div>
                        <div class="detail-item"><span>No Stamp:</span> <strong>{{ row.detail_komponen.nomor_stamp || '-' }}</strong></div>
                        <div class="detail-item"><span>Merk/Uk:</span> {{ row.detail_komponen.merk_tipe || '-' }}{{ row.detail_komponen.ukuran ? ' / ' + row.detail_komponen.ukuran : '' }}</div>
                        <div class="detail-item"><span>Jenis:</span> {{ row.detail_komponen.jenis_ban || '-' }}</div>
                        <div class="detail-item"><span>Pemasok:</span> {{ row.detail_komponen.pemasok || '-' }}</div>
                        <div class="detail-item"><span>Tgl Pasang:</span> {{ formatDate(row.detail_komponen.tanggal_pemasangan) }}</div>
                        <div class="detail-item"><span>Km Pasang:</span> {{ formatNumber(row.detail_komponen.km_pemasangan) }} km</div>
                        <div class="detail-item"><span>Status Bekas:</span> {{ row.detail_komponen.status_ban_bekas || '-' }}</div>
                        <div class="detail-item price"><span>Harga:</span> Rp {{ formatNumber(row.detail_komponen.harga) }}</div>
                      </template>
                      
                      <!-- Aki / Accu Info -->
                      <template v-else-if="isAccuType(row)">
                        <div class="detail-item"><span>No Seri:</span> <strong>{{ row.detail_komponen.nomor_seri || '-' }}</strong></div>
                        <div class="detail-item"><span>Merk/Tipe:</span> {{ row.detail_komponen.merk_tipe || '-' }}</div>
                        <div class="detail-item"><span>Tgl Pasang:</span> {{ formatDate(row.detail_komponen.tanggal_pemasangan) }}</div>
                        <div class="detail-item"><span>Pemasok:</span> {{ row.detail_komponen.pemasok || '-' }}</div>
                      </template>

                      <!-- General / Oli Info -->
                      <template v-else>
                        <div v-if="row.jumlah_liter" class="detail-item"><span>Konsumsi:</span> <strong class="badge-blue">{{ row.jumlah_liter }} Liter</strong></div>
                        <div class="detail-item"><span>Merk/Tipe:</span> {{ row.detail_komponen.merk_tipe || '-' }}</div>
                        <div class="detail-item"><span>No Seri:</span> {{ row.detail_komponen.nomor_seri || '-' }}</div>
                        <div class="detail-item"><span>Harga:</span> Rp {{ formatNumber(row.detail_komponen.harga) }}</div>
                      </template>
                    </div>
                    <div v-else-if="row.jumlah_liter" class="detail-item">
                       <span>Konsumsi:</span> <strong class="badge-blue">{{ row.jumlah_liter }} Liter</strong>
                    </div>
                    <span v-else class="text-muted">— Tidak ada detail identitas —</span>
                  </td>

                  <td>
                    <div class="notes-container">
                      <p class="notes-text" :title="row.catatan">{{ row.catatan || 'Tidak ada catatan khusus.' }}</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.total_pages > 1" class="pagination">
            <button 
              class="page-btn" 
              :disabled="pagination.current_page === 1" 
              @click="fetchHistory(pagination.current_page - 1)"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2.5"><polyline points="15,18 9,12 15,6"/></svg>
            </button>
            <div class="page-numbers">
              <button 
                v-for="p in pagination.total_pages" 
                :key="p" 
                class="page-num" 
                :class="{ active: pagination.current_page === p }"
                @click="fetchHistory(p)"
              >{{ p }}</button>
            </div>
            <button 
              class="page-btn" 
              :disabled="pagination.current_page === pagination.total_pages" 
              @click="fetchHistory(pagination.current_page + 1)"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar.vue'
import { mapState, mapActions } from 'vuex'
import axios from '@/core/axios'
import ExcelExportService from '@/services/ExcelExportService'

export default {
  name: 'MaintenanceHistoryView',
  components: { Sidebar },
  data() {
    return {
      loading: false,
      history: [],
      exporting: false,
      filters: {
        category_id: '',
        search: '',
        limit: 15
      },
      pagination: {
        current_page: 1,
        total_pages: 1,
        total: 0,
        from: 0,
        to: 0
      }
    }
  },
  computed: {
    ...mapState('kategoriKomponen', ['kategoriList']),
  },
  mounted() {
    this.fetchKategori()
    this.fetchHistory()
  },
  methods: {
    ...mapActions('kategoriKomponen', ['fetchKategori']),
    isBanType(row) {
      const name = row.komponen?.kategori?.nama_kategori?.toLowerCase() || ''
      return name.includes('ban')
    },
    isAccuType(row) {
      const name = row.komponen?.kategori?.nama_kategori?.toLowerCase() || ''
      return name.includes('aki') || name.includes('accu')
    },
    async fetchHistory(page = 1) {
      this.loading = true
      try {
        const params = {
          page,
          category_id: this.filters.category_id,
          search: this.filters.search,
          limit: this.filters.limit
        }
        const res = await axios.get('/riwayat_perawatan', { params })
        if (res.data.status === 'success') {
          const apiData = res.data.data
          this.history = apiData.data
          this.pagination = {
            current_page: apiData.current_page,
            total_pages: apiData.last_page,
            total: apiData.total,
            from: apiData.from || 0,
            to: apiData.to || 0
          }
        }
      } catch (err) {
        console.error('Fetch history error:', err)
      } finally {
        this.loading = false
      }
    },
    async handleExport() {
      this.exporting = true
      try {
        // Fetch all data matching current filters
        const params = {
          export: 'true',
          category_id: this.filters.category_id,
          search: this.filters.search
        }
        const res = await axios.get('/riwayat_perawatan', { params })
        if (res.data.status === 'success') {
          const allData = res.data.data
          const categoryName = this.filters.category_id 
            ? this.kategoriList.find(c => c.id === this.filters.category_id)?.nama_kategori 
            : 'Semua Kategori'
          
          await ExcelExportService.exportRiwayat(allData, {
            categoryName,
            filters: this.filters
          })
        }
      } catch (err) {
        console.error('Export error:', err)
      } finally {
        this.exporting = false
      }
    },
    resetFilters() {
      this.filters = { category_id: '', search: '', limit: 15 }
      this.fetchHistory(1)
    },
    calculateNo(index) {
      return (this.pagination.current_page - 1) * this.filters.limit + index + 1
    },
    formatDate(date) {
      if (!date) return '-'
      
      // Fix timezone issue when parsing YYYY-MM-DD
      const dateObj = new Date(date)
      if (isNaN(dateObj.getTime())) return date
      
      // If it's a simple date string (YYYY-MM-DD), the time will be 00:00:00 UTC
      // which shows as day before in WIB. We force local time or use parts.
      const d = date.split('T')[0].split('-')
      if (d.length === 3) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${d[2]} ${months[parseInt(d[1]) - 1]} ${d[0]}`
      }

      return dateObj.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      })
    },
    formatNumber(n) {
      return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.page-layout { display: flex; min-height: 100vh; font-family: 'Poppins', sans-serif; }
.main-content { flex: 1; background: #f0f0f8; display: flex; flex-direction: column; min-width: 0; }

/* TOPBAR */
.topbar { background: #fff; border-bottom: 1px solid #e8e8f0; min-height: 80px; box-sizing: border-box; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10; }
.page-title { font-size: 1.25rem; font-weight: 700; color: #1e1d4c; }
.page-sub { font-size: 0.78rem; color: #9ca3af; margin-top: 2px; }

/* CONTENT */
.content-body { flex: 1; padding: 1.75rem 2rem; display: flex; flex-direction: column; gap: 1.5rem; }

/* CARD */
.card { background: #fff; border-radius: 14px; border: 1px solid #e8e8f0; overflow: hidden; }
.filter-card { padding: 1.25rem 1.5rem; }

/* FILTERS */
.filter-row { display: flex; align-items: flex-end; gap: 1.25rem; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 6px; }
.filter-group label { font-size: 0.72rem; font-weight: 600; color: #3E3D90; text-transform: uppercase; letter-spacing: 0.5px; }

.search-group { flex: 1; min-width: 280px; }

select, input { padding: 0.65rem 0.9rem; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; outline: none; background: #fff; color: #374151; transition: all 0.2s; }
select:focus, input:focus { border-color: #3E3D90; box-shadow: 0 0 0 3px rgba(62,61,144,0.1); }

.search-input-wrap { position: relative; }
.search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.search-input-wrap input { width: 100%; padding-left: 33px; }

.filter-actions { display: flex; gap: 0.65rem; }

/* BUTTONS */
.btn-primary { display: flex; align-items: center; gap: 6px; padding: 0.65rem 1.25rem; background: #3E3D90; border: none; border-radius: 10px; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: all 0.15s; }
.btn-primary:hover:not(:disabled) { background: #4c4bb0; transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

.btn-secondary { padding: 0.65rem 1rem; background: #fff; border: 1.5px solid #e8e8f0; border-radius: 10px; color: #6b7280; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.15s; }
.btn-secondary:hover:not(:disabled) { background: #f5f5fb; color: #374151; }

.loader-small { width: 14px; height: 14px; border: 2px solid #fff; border-bottom-color: transparent; border-radius: 50%; display: inline-block; animation: rotation 1s linear infinite; }

.btn-success { display: flex; align-items: center; gap: 6px; padding: 0.65rem 1.25rem; background: #059669; border: none; border-radius: 10px; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(5,150,105,0.3); transition: all 0.15s; }
.btn-success:hover:not(:disabled) { background: #047857; transform: translateY(-1px); }
.btn-success:disabled { opacity: 0.7; cursor: not-allowed; }
@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* TABLE HEADER */
.table-header { padding: 1.1rem 1.5rem; border-bottom: 2px solid #f0f0f8; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.table-header-left { display: flex; align-items: center; gap: 0.75rem; }
.table-header-right { display: flex; align-items: center; gap: 1.5rem; }

.limit-control { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #64748b; font-weight: 500; }
.limit-control select { padding: 0.35rem 0.6rem; border-radius: 8px; font-size: 0.78rem; border: 1.5px solid #e2e8f0; cursor: pointer; }

.table-icon { width: 34px; height: 34px; background: #ede9fe; color: #6d28d9; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.table-title { font-size: 0.95rem; font-weight: 600; color: #1e1d4c; }
.table-stats { font-size: 0.72rem; color: #9ca3af; font-weight: 600; background: #f8f9fc; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0; }

/* TABLE */
.table-wrap { overflow-x: auto; position: relative; }
.table { width: 100%; border-collapse: collapse; min-width: 1000px; }
.table-loading { opacity: 0.6; pointer-events: none; }

.table thead th { 
  padding: 0.85rem 1.25rem; 
  font-size: 0.68rem; 
  font-weight: 700; 
  color: #3E3D90; 
  text-transform: uppercase; 
  letter-spacing: 0.08em; 
  background: #f8f9fc; 
  text-align: left; 
  border-bottom: 2px solid #e2e8f0; 
  border-right: 1px solid #e2e8f0;
}
.table thead th:last-child { border-right: none; }

.table tbody td { 
  padding: 1.25rem 1.25rem; 
  font-size: 0.82rem; 
  color: #374151; 
  border-bottom: 1px solid #edf2f7; 
  border-right: 1px solid #edf2f7;
  vertical-align: top; 
}
.table tbody td:last-child { border-right: none; }
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover td { background: #fbfbff; }

.text-center { text-align: center !important; }
.no-cell { font-weight: 700; color: #94a3b8; font-size: 0.75rem; background: #fafafa !important; }

/* CELLS */
.date-text { font-weight: 700; color: #1e1d4c; font-size: 0.78rem; white-space: nowrap; display: block; margin-top: 2px; }
.vehicle-cell { display: flex; flex-direction: column; gap: 2px; }
.nopol-text { font-weight: 700; color: #3E3D90; letter-spacing: 0.3px; font-size: 0.9rem; }
.vehicle-type { font-size: 0.68rem; color: #9ca3af; }

.comp-cell { display: flex; flex-direction: column; gap: 4px; }
.comp-name { font-weight: 600; color: #1e1d4c; font-size: 0.85rem; }
.category-badge { font-size: 0.62rem; color: #6d28d9; background: #f3f0ff; padding: 2px 6px; border-radius: 5px; width: fit-content; text-transform: uppercase; font-weight: 700; border: 1px solid #e0d9ff; }

.km-cell { font-weight: 700; color: #1e1d4c; white-space: nowrap; }
.km-cell .unit { font-size: 0.7rem; color: #9ca3af; font-weight: 400; margin-left: 2px; }

/* DETAIL GRID */
.history-detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem 1rem; min-width: 320px; }
.detail-item { font-size: 0.74rem; line-height: 1.4; display: flex; gap: 6px; color: #1e1d4c; }
.detail-item span { color: #94a3b8; font-weight: 500; white-space: nowrap; min-width: 75px; }
.detail-item.price { border-top: 1px dashed #f0f0f8; padding-top: 4px; grid-column: 1 / -1; font-weight: 600; color: #059669; }

.badge-blue { background: #dbeafe; color: #1e40af; padding: 1px 6px; border-radius: 4px; font-size: 0.72rem; }

/* NOTES */
.notes-container { max-width: 250px; }
.notes-text { font-size: 0.76rem; color: #64748b; line-height: 1.5; margin: 0; }

.text-muted { color: #d1d5db; font-style: italic; font-size: 0.75rem; }

/* PAGINATION */
.pagination { padding: 1rem 1.5rem; display: flex; justify-content: center; align-items: center; gap: 1rem; border-top: 1px solid #f0f0f8; }
.page-btn { width: 32px; height: 32px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.page-btn:hover:not(:disabled) { border-color: #3E3D90; color: #3E3D90; background: #f0f0fb; }
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.page-numbers { display: flex; gap: 0.3rem; }
.page-num { width: 32px; height: 32px; border: none; background: none; color: #64748b; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.2s; }
.page-num:hover { background: #f5f5fb; }
.page-num.active { background: #3E3D90; color: #fff; font-weight: 600; }

.empty-state { text-align: center; padding: 4rem !important; color: #9ca3af; }
.empty-icon { margin-bottom: 1rem; color: #d1d5db; }

@media (max-width: 1024px) {
  .history-detail-grid { grid-template-columns: 1fr; }
}
</style>
