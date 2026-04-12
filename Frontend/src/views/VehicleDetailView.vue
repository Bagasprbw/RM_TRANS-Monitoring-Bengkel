<template>
  <div class="page-layout">
    <Sidebar />
    <main class="main-content">

      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="back-btn" @click="$router.push('/monitoring-kendaraan')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18" stroke-width="2"><polyline points="15,18 9,12 15,6"/></svg>
          </button>
          <div>
            <div class="vehicle-title-row">
              <h1 class="page-title">{{ vehicle.plat_nomor }}</h1>
              <span class="status-badge">Aktif</span>
            </div>
            <p class="page-sub">{{ vehicle.jenis_kendaraan }}</p>
          </div>
        </div>

        <div class="km-card" @click="showUpdateKm = true" title="Klik untuk update KM">
          <p class="km-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="10" height="10" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Total Kilometer
          </p>
          <div class="km-row">
            <span class="km-value">{{ formatNumber(vehicle.total_km) }}</span>
            <span class="km-unit">km</span>
            <span class="edit-km-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </span>
          </div>
        </div>
      </header>

      <div class="content-body">
        <div class="card">

          <!-- Section Header -->
          <div class="section-header">
            <div>
              <h2 class="section-title">Component Monitoring</h2>
              <p class="section-sub">{{ filteredComponents.length }} komponen ditampilkan</p>
            </div>
            <div class="section-actions">
              <div class="filter-tabs">
                <button
                  v-for="cat in dynamicFilterCategories"
                  :key="cat.value"
                  @click="selectedFilter = cat.value"
                  class="filter-tab"
                  :class="{ active: selectedFilter === cat.value }"
                >{{ cat.label }}</button>
              </div>
              <button class="btn-secondary" @click="showAddCategory = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="13" height="13" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Kategori
              </button>
              <button class="btn-primary" @click="showAddComponent = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="13" height="13" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Komponen
              </button>
            </div>
          </div>

          <!-- Search -->
          <div class="search-row">
            <div class="search-wrap">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input v-model="searchQuery" type="text" placeholder="Cari komponen..." />
            </div>
          </div>

          <!-- Component List -->
          <div class="component-list">
            <div v-if="loadingComponents" class="loading-state">
                <p>Memuat komponen...</p>
            </div>
            <div v-else-if="filteredComponents.length === 0" class="empty-state">
              <h4>Komponen Kosong</h4>
              <p>Tidak ada data komponen yang ditemukan untuk kendaraan ini.</p>
            </div>

            <div
              v-else
              v-for="comp in filteredComponents"
              :key="comp.id"
              class="component-item"
              :class="{'row-danger': comp.health < 30}"
            >
              <div class="comp-icon" :class="getIconClass(comp.health)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6m5.2-13.2l-4.2 4.2m0 6l4.2 4.2M23 12h-6m-6 0H1"/></svg>
              </div>
              <div class="comp-info">
                <div class="comp-name-row">
                  <span class="comp-name">{{ comp.nama_komponen }}</span>
                  <span class="comp-category">{{ comp.kategori?.nama_kategori }}</span>
                  <span v-if="comp.has_identity" class="identity-badge" :title="comp.detail[0]?.nomor_seri">
                    No seri: {{ comp.detail[0]?.nomor_seri || '-' }}
                  </span>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill" :class="getBarClass(comp.health)" :style="{ width: comp.health + '%' }"></div>
                </div>
                <div class="comp-stats">
                  <span :class="getHealthClass(comp.health)">{{ comp.health }}% health</span>
                  <span class="dot">·</span>
                  <span class="remaining-text">{{ comp.remaining }}</span>
                  <span class="dot">·</span>
                  <span class="tracking-type">{{ comp.tipe_pelacakan.toUpperCase() }}</span>
                </div>
              </div>
              <div class="comp-actions">
                <button class="reset-btn" @click="openResetModal(comp)">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  Reset
                </button>
                <button class="icon-btn blue" title="Edit" @click="selectedComponent = comp; showEditComponent = true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="icon-btn delete" title="Hapus" @click="deleteComp(comp.id)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="14" height="14" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19,6V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modals -->
    <UpdateKilometerModal 
      :isOpen="showUpdateKm" 
      :currentKm="vehicle.total_km || 0" 
      :monitoringId="$route.params.id"
      :armadaId="vehicle.armada_id"
      @close="showUpdateKm = false" 
      @updated="onKmUpdated" 
    />
    <AddComponentModal :isOpen="showAddComponent" :monitoringId="$route.params.id" @close="showAddComponent = false" @component-added="loadComponents" />
    <AddCategoryModal :isOpen="showAddCategory" @close="showAddCategory = false" />
    
    <!-- Reset Component Modal -->
    <transition name="modal-fade">
        <div v-if="showResetModal" class="modal-overlay" @click.self="showResetModal = false">
            <div class="modal-box reset-box">
                <div class="modal-header">
                    <div class="modal-header-left">
                        <div class="modal-icon reset-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        </div>
                        <div>
                            <h3 class="modal-title">Reset / Ganti Komponen</h3>
                            <p class="modal-sub">{{ resettingComp?.nama_komponen }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <p class="reset-desc">Mereset komponen akan mencatat riwayat perawatan dan memulai siklus baru dari 100%.</p>
                    
                    <div class="form-group">
                        <label>Catatan Perawatan</label>
                        <textarea v-model="resetForm.catatan" placeholder="Contoh: Ganti ban baru karena sudah botak"></textarea>
                    </div>

                    <div class="grid-form">
                        <div class="form-group">
                            <label>Tgl Lepas (Lama) <span class="req">*</span></label>
                            <input v-model="resetForm.tanggal_pelepasan" type="date" />
                        </div>
                        <div v-if="resettingComp?.kategori?.nama_kategori.toLowerCase().includes('oli')" class="form-group">
                            <label>Jumlah Liter</label>
                            <input v-model.number="resetForm.jumlah_liter" type="number" step="0.1" />
                        </div>
                    </div>

                    <!-- If it had identity, and user wants to provide replacement -->
                    <div v-if="resettingComp?.has_identity" class="replacement-section">
                        <div class="replacement-header">Identitas Baru (Pengganti)</div>
                        
                        <div class="grid-form">
                            <div class="form-group">
                                <label>No Seri Baru</label>
                                <input v-model="resetForm.new_detail.nomor_seri" type="text" />
                            </div>
                            <div class="form-group" v-if="resettingComp?.kategori?.nama_kategori.toLowerCase().includes('ban')">
                                <label>No Stamp Baru</label>
                                <input v-model="resetForm.new_detail.nomor_stamp" type="text" />
                            </div>
                        </div>

                        <div v-if="resettingComp?.kategori?.nama_kategori.toLowerCase().includes('ban')" class="grid-form" style="margin-top: 10px;">
                            <div class="form-group">
                                <label>Jenis Ban</label>
                                <select v-model="resetForm.new_detail.jenis_ban">
                                    <option value="ORI">ORI</option>
                                    <option value="VULK">VULK</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ukuran</label>
                                <input v-model="resetForm.new_detail.ukuran" type="text" />
                            </div>
                        </div>

                        <div class="grid-form" style="margin-top: 10px;">
                            <div class="form-group">
                                <label>Merk / Tipe</label>
                                <input v-model="resetForm.new_detail.merk_tipe" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Pemasok</label>
                                <input v-model="resetForm.new_detail.pemasok" type="text" />
                            </div>
                        </div>

                        <div class="grid-form" style="margin-top: 10px;">
                            <div class="form-group">
                                <label>Harga (Rp)</label>
                                <input v-model.number="resetForm.new_detail.harga" type="number" />
                            </div>
                            <div class="form-group">
                                <label>Tgl Pasang Baru</label>
                                <input v-model="resetForm.new_detail.tanggal_pemasangan" type="date" />
                            </div>
                        </div>

                        <div v-if="resettingComp?.kategori?.nama_kategori.toLowerCase().includes('ban')" class="form-group" style="margin-top: 10px;">
                            <label>KM Pasang Baru</label>
                            <input v-model.number="resetForm.new_detail.km_pemasangan" type="number" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" @click="showResetModal = false">Batal</button>
                    <button class="btn-submit reset-submit" @click="submitReset">Reset Sekarang</button>
                </div>
            </div>
        </div>
    </transition>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar.vue'
import AddCategoryModal from '@/components/AddCategoryModal.vue'
import AddComponentModal from '@/components/AddComponentModal.vue'
import UpdateKilometerModal from '@/components/UpdateKilometerModal.vue'
import componentService from '@/modules/monitoring/services/componentService'
import { mapState, mapActions } from 'vuex'
import Swal from 'sweetalert2'

export default {
  name: 'VehicleDetailView',
  components: { Sidebar, AddCategoryModal, AddComponentModal, UpdateKilometerModal },
  data() {
    return {
      searchQuery: '',
      selectedFilter: 'all',
      showUpdateKm: false,
      showAddComponent: false,
      showEditComponent: false,
      showAddCategory: false,
      showResetModal: false,
      loadingComponents: false,
      components: [],
      resettingComp: null,
      resetForm: {
          catatan: '',
          jumlah_liter: null,
          tanggal_pelepasan: '',
          new_detail: {
              nomor_seri: '',
              nomor_stamp: '',
              jenis_ban: 'ORI',
              ukuran: '',
              merk_tipe: '',
              pemasok: '',
              harga: 0,
              tanggal_pemasangan: '',
              km_pemasangan: 0
          }
      }
    }
  },
  computed: {
    ...mapState('monitoring', ['currentMonitoring']),
    ...mapState('kategoriKomponen', ['kategoriList']),
    dynamicFilterCategories() {
      const filters = [{ label: 'Semua', value: 'all' }];
      this.kategoriList.forEach(item => {
         filters.push({ label: item.nama_kategori, value: item.id });
      });
      return filters;
    },
    vehicle() {
      if (!this.currentMonitoring) return {}
      return {
        id: this.currentMonitoring.id,
        armada_id: this.currentMonitoring.armada_id,
        plat_nomor: this.currentMonitoring.armada?.nopol || 'Unknown',
        jenis_kendaraan: this.currentMonitoring.armada?.jenis?.nama_jenis || 'Unknown',
        total_km: this.currentMonitoring.last_recorded_km || 0
      }
    },
    filteredComponents() {
      let list = this.components
      if (this.selectedFilter !== 'all') {
          list = list.filter(c => c.kategori_komponen_id === this.selectedFilter)
      }
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase()
        list = list.filter(c => 
            c.nama_komponen.toLowerCase().includes(q) || 
            c.kategori?.nama_kategori.toLowerCase().includes(q)
        )
      }
      return [...list].sort((a, b) => a.health - b.health)
    }
  },
  methods: {
    ...mapActions('monitoring', ['fetchMonitoringDetail']),
    ...mapActions('kategoriKomponen', ['fetchKategori']),
    formatNumber(n) { return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') },
    getIconClass(h) { if (h < 30) return 'icon-red'; if (h < 60) return 'icon-orange'; return 'icon-green' },
    getBarClass(h) { if (h < 30) return 'bg-red'; if (h < 60) return 'bg-orange'; return 'bg-green' },
    getHealthClass(h) { if (h < 30) return 'text-red'; if (h < 60) return 'text-orange'; return 'text-green' },
    
    async loadComponents() {
        this.loadingComponents = true;
        try {
            const res = await componentService.getComponents(this.$route.params.id);
            this.components = res.data.data;
        } catch (err) {
            console.error(err);
        } finally {
            this.loadingComponents = false;
        }
    },

    onKmUpdated() {
        this.fetchMonitoringDetail(this.$route.params.id);
        this.loadComponents(); // update health based on new KM
    },

    openResetModal(comp) {
        this.resettingComp = comp;
        const today = new Date().toISOString().substr(0, 10);
        this.resetForm = {
            catatan: '',
            jumlah_liter: null,
            tanggal_pelepasan: today,
            new_detail: { 
                nomor_seri: '', 
                nomor_stamp: '',
                jenis_ban: 'ORI',
                ukuran: '',
                merk_tipe: '',
                pemasok: '',
                harga: 0,
                tanggal_pemasangan: today,
                km_pemasangan: this.vehicle.total_km
            }
        };
        this.showResetModal = true;
    },

    async submitReset() {
        try {
            const payload = {
                catatan: this.resetForm.catatan,
                jumlah_liter: this.resetForm.jumlah_liter,
                tanggal_pelepasan: this.resetForm.tanggal_pelepasan
            };
            
            // Only send new_detail if user typed a serial number
            if (this.resetForm.new_detail.nomor_seri) {
                payload.new_detail = { ...this.resetForm.new_detail };
            }

            const res = await componentService.resetComponent(this.resettingComp.id, payload);
            if (res.data.status === 'success') {
                Swal.fire('Berhasil', 'Komponen telah direset ke 100%', 'success');
                this.showResetModal = false;
                this.loadComponents();
            }
        } catch (err) {
            const msg = err.response?.data?.message || 'Gagal mereset komponen';
            Swal.fire('Error', msg, 'error');
        }
    },

    async deleteComp(id) {
        const result = await Swal.fire({
            title: 'Hapus Komponen?',
            text: 'Data riwayat juga akan hilang!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus'
        });
        
        if (result.isConfirmed) {
            try {
                await componentService.deleteComponent(id);
                this.loadComponents();
                Swal.fire('Terhapus', 'Komponen berhasil dihapus', 'success');
            } catch (err) {
                const msg = err.response?.data?.message || 'Gagal menghapus komponen';
                Swal.fire('Gagal', msg, 'error');
            }
        }
    }
  },
  mounted() {
    this.fetchMonitoringDetail(this.$route.params.id);
    this.loadComponents();
    this.fetchKategori();
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.page-layout { display: flex; min-height: 100vh; font-family: 'Poppins', sans-serif; }
.main-content { flex: 1; background: #f0f0f8; display: flex; flex-direction: column; min-width: 0; }

/* TOPBAR */
.topbar { background: #fff; border-bottom: 1px solid #e8e8f0; min-height: 80px; box-sizing: border-box; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; position: sticky; top: 0; z-index: 10; }
.topbar-left { display: flex; align-items: center; gap: 0.85rem; }
.back-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border: 1.5px solid #e8e8f0; background: #f5f5fb; border-radius: 9px; cursor: pointer; color: #374151; transition: all 0.15s; flex-shrink: 0; }
.back-btn:hover { background: #ede9fe; border-color: #3E3D90; color: #3E3D90; }
.vehicle-title-row { display: flex; align-items: center; gap: 0.65rem; }
.page-title { font-size: 1.25rem; font-weight: 700; color: #1e1d4c; }
.status-badge { padding: 0.2rem 0.65rem; background: #dcfce7; color: #15803d; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
.page-sub { font-size: 0.78rem; color: #9ca3af; margin-top: 2px; }

.km-card { background: #fff; border: 1.5px solid #e2e8f0; border-left: 3px solid #3E3D90; border-radius: 8px; padding: 0.5rem 0.95rem; min-width: 150px; cursor: pointer; transition: all 0.2s ease; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.km-card:hover { border-color: #3E3D90; box-shadow: 0 4px 12px rgba(62,61,144,0.08); transform: translateY(-1px); }
.km-label { font-size: 0.6rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; display: flex; align-items: center; gap: 3px; }
.km-row { display: flex; align-items: baseline; gap: 4px; }
.km-value { font-size: 1.25rem; font-weight: 700; color: #1e1d4c; line-height: 1.15; }
.km-unit { font-size: 0.75rem; font-weight: 600; color: #64748b; }
.edit-km-btn { margin-left: auto; color: #94a3b8; display: flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 5px; transition: all 0.2s; align-self: center; background: #f8fafc; }
.km-card:hover .edit-km-btn { background: #3E3D90; color: #ffffff; }

/* CONTENT */
.content-body { flex: 1; padding: 1.75rem 2rem; }
.card { background: #fff; border-radius: 14px; border: 1px solid #e8e8f0; overflow: hidden; }

/* SECTION HEADER */
.section-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid #f0f0f8; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
.section-title { font-size: 0.95rem; font-weight: 600; color: #1e1d4c; }
.section-sub { font-size: 0.72rem; color: #9ca3af; margin-top: 1px; }
.section-actions { display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; }

.filter-tabs { display: flex; gap: 3px; background: #f5f5fb; border-radius: 9px; padding: 3px; overflow-x: auto; max-width: 300px; }
.filter-tab { padding: 0.3rem 0.7rem; border: none; background: none; border-radius: 7px; font-family: 'Poppins', sans-serif; font-size: 0.78rem; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
.filter-tab.active { background: #fff; color: #3E3D90; font-weight: 600; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }

.btn-secondary { display: flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; background: #f0f0f8; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.8rem; font-weight: 600; color: #374151; cursor: pointer; transition: all 0.15s; }
.btn-primary { display: flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; background: #3E3D90; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.8rem; font-weight: 600; color: #fff; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: all 0.15s; }

/* SEARCH ROW */
.search-row { padding: 0.85rem 1.5rem; border-bottom: 1px solid #f0f0f8; }
.search-wrap { position: relative; max-width: 300px; }
.search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.search-wrap input { width: 100%; padding: 0.6rem 1rem 0.6rem 32px; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; outline: none; background: #fff; color: #374151; box-sizing: border-box; }

/* COMPONENTS */
.loading-state { padding: 3rem; text-align: center; color: #9ca3af; animation: pulse 1.5s infinite; }
@keyframes pulse { 0% { opacity: 0.5; } 50% { opacity: 1; } 100% { opacity: 0.5; } }

.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; text-align: center; background: #faf9ff; border-radius: 12px; border: 2px dashed #e8e8f0; margin: 1.5rem; }
.empty-state h4 { font-size: 1.05rem; font-weight: 600; color: #64748b; margin: 0 0 0.25rem 0; }
.empty-state p { font-size: 0.85rem; color: #94a3b8; margin: 0; max-width: 300px; line-height: 1.5; }

.component-item { display: flex; align-items: center; gap: 1rem; padding: 1.1rem 1.5rem; border-bottom: 1px solid #f5f5fb; transition: background 0.1s; }
.component-item:hover { background: #faf9ff; }

.comp-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.icon-red { background: #fff0f0; color: #ef4444; }
.icon-orange { background: #fff7ed; color: #f97316; }
.icon-green { background: #f0fdf4; color: #22c55e; }

.comp-info { flex: 1; min-width: 0; }
.comp-name-row { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; flex-wrap: wrap; }
.comp-name { font-size: 0.85rem; font-weight: 600; color: #1e1d4c; }
.comp-category { padding: 2px 7px; background: #f5f5fb; border: 1px solid #e8e8f0; color: #6b7280; border-radius: 5px; font-size: 0.68rem; font-weight: 500; }
.identity-badge { font-size: 0.65rem; color: #3E3D90; background: #ede9fe; padding: 2px 6px; border-radius: 4px; font-weight: 600; cursor: help; }

.row-danger { background-color: #fff0f0 !important; border-left: 3px solid #ef4444; }
.row-danger:hover { background-color: #ffe4e4 !important; }

.progress-bar { background: #f0f0f8; border-radius: 999px; height: 6px; margin-bottom: 6px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 999px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
.bg-green { background: #22c55e; }
.bg-orange { background: #f97316; }
.bg-red { background: #ef4444; }

.comp-stats { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; flex-wrap: wrap; }
.dot { color: #d1d5db; }
.remaining-text { color: #525252; font-weight: 500; }
.tracking-type { color: #9ca3af; font-size: 0.65rem; letter-spacing: 0.5px; }
.text-green { color: #16a34a; font-weight: 600; }
.text-orange { color: #ea580c; font-weight: 600; }
.text-red { color: #dc2626; font-weight: 600; }

.comp-actions { display: flex; gap: 4px; flex-shrink: 0; align-items: center; }
.reset-btn { display: flex; align-items: center; gap: 5px; padding: 0.4rem 0.75rem; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.15s; }
.reset-btn:hover { background: #dcfce7; transform: translateY(-1px); }

.icon-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; background: none; border-radius: 8px; cursor: pointer; color: #b0b0c8; transition: all 0.15s; }
.icon-btn.blue:hover { color: #3E3D90; background: #f0f0fb; }
.icon-btn.delete:hover { color: #ef4444; background: #fff0f0; }

/* MODAL STYLES */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,15,40,0.55); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 1rem; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 480px; box-shadow: 0 24px 64px rgba(0,0,0,0.18); font-family: 'Poppins', sans-serif; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; }
.reset-box { max-width: 650px; border-top: 5px solid #3b82f6; }

.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f0f0f8; }
.modal-header-left { display: flex; align-items: center; gap: 0.7rem; }
.modal-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.reset-icon { background: #eff6ff; color: #3b82f6; }
.modal-title { font-size: 1rem; font-weight: 600; color: #1e1d4c; }
.modal-sub { font-size: 0.75rem; color: #9ca3af; }

.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.2rem; overflow-y: auto; flex: 1; }
.reset-desc { font-size: 0.85rem; color: #4b5563; line-height: 1.5; }

.form-group { display: flex; flex-direction: column; gap: 5px; }
label { font-size: 0.75rem; font-weight: 600; color: #3E3D90; text-transform: uppercase; letter-spacing: 0.5px; }
textarea, input { padding: 0.75rem 1rem; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.875rem; outline: none; transition: border-color 0.2s; }
textarea { min-height: 80px; resize: none; }
input:focus, textarea:focus { border-color: #3E3D90; }

.replacement-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-top: 0.5rem; }
.replacement-header { font-size: 0.7rem; font-weight: 700; color: #3E3D90; text-transform: uppercase; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 5px; }
.grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.1rem 1.4rem; border-top: 1px solid #f0f0f8; }
.btn-cancel { padding: 0.6rem 1.2rem; background: #fff; border: 1.5px solid #e8e8f0; border-radius: 10px; font-weight: 500; cursor: pointer; color: #6b7280; transition: all 0.15s; }
.btn-submit { padding: 0.6rem 1.2rem; background: #3E3D90; color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: all 0.15s; }
.reset-submit { background: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.3); }
.reset-submit:hover { background: #2563eb; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .topbar { padding: 0 1rem; height: auto; padding-top: 1rem; padding-bottom: 1rem; flex-direction: column; align-items: flex-start; }
  .km-card { width: 100%; box-sizing: border-box; }
  .content-body { padding: 1rem; }
  .section-actions { width: 100%; justify-content: space-between; }
  .filter-tabs { max-width: 100%; }
}
</style>