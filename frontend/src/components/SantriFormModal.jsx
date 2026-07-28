import { useState, useEffect } from 'react';
import axios from 'axios';
import { X, Save, UploadCloud } from 'lucide-react';

const SantriFormModal = ({ isOpen, onClose, kds = null, onSuccess }) => {
  const [formData, setFormData] = useState({
    stambuk: '',
    nama: '',
    kelas: '',
    negara: 'Indonesia',
    kewarganegaraan: 'WNI',
    pondok: '',
    kepengurusan: '',
    rayon: '',
    no_paspor: '',
    exp_paspor: '',
    exp_itas: '',
  });
  
  const [loading, setLoading] = useState(false);
  const [photo, setPhoto] = useState(null);
  const [photoPreview, setPhotoPreview] = useState('');

  useEffect(() => {
    if (isOpen) {
      if (kds) {
        // Fetch existing data
        setLoading(true);
        axios.get(`/api/santri/${kds}`)
          .then(res => {
            if (res.data.success) {
              const { santri, paspor, itas } = res.data;
              setFormData({
                stambuk: santri.stambuk || '',
                nama: santri.nama || '',
                kelas: santri.kelas || '',
                negara: santri.negara || 'Indonesia',
                kewarganegaraan: santri.kewarganegaraan || 'WNI',
                pondok: santri.pondok || '',
                kepengurusan: santri.kepengurusan || '',
                rayon: santri.rayon || '',
                no_paspor: paspor?.no_paspor || '',
                exp_paspor: paspor?.exp_paspor || '',
                exp_itas: itas?.exp_itas || '',
              });
              if (santri.foto_url) {
                setPhotoPreview(santri.foto_url);
              }
            }
          })
          .finally(() => setLoading(false));
      } else {
        // Reset form
        setFormData({
          stambuk: '', nama: '', kelas: '', negara: 'Indonesia', kewarganegaraan: 'WNI',
          pondok: '', kepengurusan: '', rayon: '', no_paspor: '', exp_paspor: '', exp_itas: ''
        });
        setPhotoPreview('');
        setPhoto(null);
      }
    }
  }, [isOpen, kds]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    let updates = { [name]: value };
    
    // Auto detect kewarganegaraan
    if (name === 'negara') {
      if (value.toLowerCase() === 'indonesia') updates.kewarganegaraan = 'WNI';
      else updates.kewarganegaraan = formData.exp_itas ? 'WNA' : 'Affidavit';
    }
    if (name === 'exp_itas' && formData.negara.toLowerCase() !== 'indonesia') {
      updates.kewarganegaraan = value ? 'WNA' : 'Affidavit';
    }

    setFormData(prev => ({ ...prev, ...updates }));
  };

  const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setPhoto(file);
      const reader = new FileReader();
      reader.onloadend = () => setPhotoPreview(reader.result);
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    const formDataObj = new FormData();
    Object.keys(formData).forEach(key => formDataObj.append(key, formData[key]));
    if (photo) formDataObj.append('foto_santri', photo);

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const url = kds ? `/api/santri/${kds}/update` : '/api/santri/store';
      
      const res = await axios.post(url, formDataObj, {
        headers: { 'X-CSRF-Token': csrf, 'Content-Type': 'multipart/form-data' }
      });
      
      if (res.data.success) {
        onSuccess();
        onClose();
      } else {
        alert(res.data.message);
      }
    } catch (err) {
      alert('Terjadi kesalahan saat menyimpan data');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div className="flex items-center justify-between p-5 border-b border-slate-200">
          <h2 className="text-xl font-bold text-slate-800">{kds ? 'Edit Data Santri' : 'Tambah Santri Baru'}</h2>
          <button onClick={onClose} className="p-2 text-slate-400 hover:bg-slate-100 rounded-full transition-colors">
            <X size={20} />
          </button>
        </div>
        
        <div className="flex-1 overflow-y-auto p-6">
          {loading && kds ? (
            <div className="flex justify-center items-center h-40">Memuat data...</div>
          ) : (
            <form id="santriForm" onSubmit={handleSubmit} className="space-y-6">
              {/* Identitas Section */}
              <div>
                <h3 className="text-lg font-semibold text-slate-800 mb-4 border-b pb-2">Identitas Utama</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span className="text-danger">*</span></label>
                    <input required name="nama" value={formData.nama} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Stambuk <span className="text-danger">*</span></label>
                    <input required name="stambuk" value={formData.stambuk} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Kelas <span className="text-danger">*</span></label>
                    <input required name="kelas" value={formData.kelas} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Rayon</label>
                    <input name="rayon" value={formData.rayon} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                </div>
              </div>

              {/* Dokumen Section */}
              <div>
                <h3 className="text-lg font-semibold text-slate-800 mb-4 border-b pb-2">Kewarganegaraan & Dokumen</h3>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Negara Asal <span className="text-danger">*</span></label>
                    <input required name="negara" value={formData.negara} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Kewarganegaraan</label>
                    <input readOnly name="kewarganegaraan" value={formData.kewarganegaraan} className="w-full px-3 py-2 border border-slate-300 bg-slate-50 rounded-lg text-slate-500" />
                  </div>
                  <div className="md:row-span-2">
                    <label className="block text-sm font-medium text-slate-700 mb-1">Foto Santri</label>
                    <div className="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:bg-slate-50 transition-colors cursor-pointer relative">
                      <input type="file" accept="image/jpeg, image/png" onChange={handlePhotoChange} className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                      {photoPreview ? (
                        <img src={photoPreview} alt="Preview" className="mx-auto h-32 object-contain" />
                      ) : (
                        <div className="py-6 flex flex-col items-center">
                          <UploadCloud className="text-slate-400 mb-2" size={32} />
                          <span className="text-sm text-primary font-medium">Pilih Foto</span>
                        </div>
                      )}
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">No Paspor</label>
                    <input name="no_paspor" value={formData.no_paspor} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Exp Paspor</label>
                    <input type="date" name="exp_paspor" value={formData.exp_paspor} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">Exp ITAS</label>
                    <input type="date" name="exp_itas" value={formData.exp_itas} onChange={handleChange} className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" />
                  </div>
                </div>
              </div>
            </form>
          )}
        </div>
        
        <div className="p-5 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
          <button onClick={onClose} className="px-5 py-2 rounded-lg text-slate-600 font-medium hover:bg-slate-200 transition-colors">Batal</button>
          <button onClick={handleSubmit} disabled={loading} className="px-5 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90 flex items-center gap-2 disabled:opacity-50 transition-colors">
            <Save size={18} /> {loading ? 'Menyimpan...' : 'Simpan Data'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default SantriFormModal;
