<template>
  <div class="page-layout">
    <Sidebar />
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h2 class="page-title">Profil Pengguna</h2>
          <p class="page-sub">Kelola informasi akun Anda</p>
        </div>
      </header>

      <div class="content-body">
        <div class="card profile-card">
          <div class="profile-header">
            <div class="avatar-circle">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="36" height="36">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </div>
            <div>
              <h3>{{ currentUser?.name || 'Administrator' }}</h3>
              <p>Admin / Super User</p>
            </div>
          </div>
          
          <div class="profile-form">
            <h4 class="form-title">Ganti Kredensial (Frontend Only)</h4>
            
            <div class="form-group">
              <label>Username Anda</label>
              <input type="text" v-model="form.username" placeholder="Masukkan username baru" />
            </div>

            <div class="grid-form">
              <div class="form-group">
                <label>Password Baru</label>
                <input type="password" v-model="form.password" placeholder="Masukkan password baru" />
              </div>
              <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" v-model="form.password_confirmation" placeholder="Ulangi password baru" />
              </div>
            </div>

            <div class="form-actions">
              <button class="btn-submit" @click="saveProfile">
                Simpan Perubahan
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar.vue'
import Swal from 'sweetalert2'

export default {
  name: 'ProfileView',
  components: { Sidebar },
  data() {
    return {
      form: {
        username: '',
        password: '',
        password_confirmation: ''
      }
    }
  },
  computed: {
    currentUser() {
      return this.$store.state.auth.user
    }
  },
  mounted() {
    if (this.currentUser && this.currentUser.username) {
      this.form.username = this.currentUser.username
    } else {
      this.form.username = 'admin' // dummy data
    }
  },
  methods: {
    saveProfile() {
      if(this.form.password && this.form.password !== this.form.password_confirmation) {
          Swal.fire('Gagal', 'Konfirmasi password tidak cocok', 'error');
          return;
      }
      
      // Karena murni frontend, kita munculkan info
      Swal.fire({
          title: 'Fitur Belum Tersedia',
          text: 'Tampilan profil sudah siap, namun fitur simpan membutuhkan API Backend yang belum ada.',
          icon: 'info',
          confirmButtonColor: '#3E3D90'
      });
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.page-layout { display: flex; min-height: 100vh; font-family: 'Poppins', sans-serif; }
.main-content { flex: 1; background: #f0f0f8; display: flex; flex-direction: column; min-width: 0; }

.topbar { background: #fff; border-bottom: 1px solid #e8e8f0; min-height: 80px; box-sizing: border-box; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10; }
.page-title { font-size: 1.25rem; font-weight: 700; color: #1e1d4c; }
.page-sub { font-size: 0.78rem; color: #9ca3af; margin-top: 2px; }

.content-body { flex: 1; padding: 1.75rem 2rem; display: flex; justify-content: center; align-items: flex-start; }

.card { background: #fff; border-radius: 14px; border: 1px solid #e8e8f0; overflow: hidden; width: 100%; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }

.profile-header { display: flex; align-items: center; gap: 1rem; padding: 2rem; background: #faf9ff; border-bottom: 1px dashed #e8e8f0; }
.avatar-circle { width: 64px; height: 64px; background: #3E3D90; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 12px rgba(62,61,144,0.3); }
.profile-header h3 { margin: 0; font-size: 1.15rem; font-weight: 600; color: #1e1d4c; }
.profile-header p { margin: 3px 0 0 0; font-size: 0.8rem; color: #64748b; font-weight: 500; }

.profile-form { padding: 2rem; display: flex; flex-direction: column; gap: 1.25rem; }
.form-title { font-size: 0.9rem; font-weight: 600; color: #3E3D90; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f0f0f8; padding-bottom: 0.5rem; margin-bottom: 0.5rem; }

.form-group { display: flex; flex-direction: column; gap: 6px; }
label { font-size: 0.75rem; font-weight: 600; color: #374151; }
input { padding: 0.75rem 1rem; border: 1.5px solid #e8e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; outline: none; background: #fff; color: #1e1d4c; transition: border-color 0.2s, box-shadow 0.2s; }
input:focus { border-color: #3E3D90; box-shadow: 0 0 0 3px rgba(62,61,144,0.1); }

.grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

.form-actions { margin-top: 1rem; display: flex; justify-content: flex-end; }
.btn-submit { padding: 0.75rem 1.5rem; background: #3E3D90; color: #fff; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(62,61,144,0.3); transition: all 0.15s; }
.btn-submit:hover { background: #4c4bb0; transform: translateY(-1px); }

@media (max-width: 600px) {
  .grid-form { grid-template-columns: 1fr; }
  .profile-header { padding: 1.5rem; }
  .profile-form { padding: 1.5rem; }
  .topbar { padding: 1rem 1rem 1rem 4rem; height: auto; }
}
</style>
