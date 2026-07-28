import { useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { 
  useReactTable, 
  getCoreRowModel, 
  getFilteredRowModel,
  getPaginationRowModel,
  flexRender 
} from '@tanstack/react-table';
import { FileText, Download, Trash2, Search, UploadCloud, FileType, CheckCircle } from 'lucide-react';

const Pemberkasan = () => {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [globalFilter, setGlobalFilter] = useState('');
  const [isUploading, setIsUploading] = useState(false);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const res = await axios.get('/api/pemberkasan');
      if (res.data.success) setData(res.data.data.berkas);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!confirm('Yakin ingin menghapus berkas ini?')) return;
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const res = await axios.post(`/api/berkas/${id}/delete`, {}, { headers: { 'X-CSRF-Token': csrf }});
      if (res.data.success) fetchData();
    } catch (err) {
      console.error(err);
    }
  };

  const handleFileUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploading(true);
    const formData = new FormData();
    formData.append('file_upload', file);
    formData.append('nama_berkas', file.name.split('.')[0]); // Default name

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const res = await axios.post('/api/berkas/upload', formData, {
        headers: { 'X-CSRF-Token': csrf, 'Content-Type': 'multipart/form-data' }
      });
      if (res.data.success) {
        fetchData();
      } else {
        alert(res.data.message);
      }
    } catch (err) {
      console.error(err);
      alert('Gagal mengupload berkas');
    } finally {
      setIsUploading(false);
      e.target.value = null; // reset input
    }
  };

  const columns = useMemo(() => [
    {
      accessorKey: 'nama_berkas',
      header: 'Nama Berkas',
      cell: ({ row }) => (
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 rounded bg-primary/10 text-primary flex items-center justify-center">
            <FileType size={16} />
          </div>
          <span className="font-medium text-slate-700">{row.original.nama_berkas}</span>
        </div>
      )
    },
    { accessorKey: 'tipe_berkas', header: 'Ekstensi' },
    { accessorKey: 'ukuran', header: 'Ukuran (KB)', cell: ({ row }) => `${row.original.ukuran} KB` },
    { accessorKey: 'tgl_upload', header: 'Tanggal Upload' },
    {
      id: 'actions',
      header: 'Aksi',
      cell: ({ row }) => (
        <div className="flex items-center gap-3">
          <a href={row.original.path_url} target="_blank" rel="noreferrer" className="text-primary hover:text-primary/80" title="Download">
            <Download size={18} />
          </a>
          <button onClick={() => handleDelete(row.original.id)} className="text-danger hover:text-danger/80" title="Hapus">
            <Trash2 size={18} />
          </button>
        </div>
      )
    }
  ], []);

  const table = useReactTable({
    data,
    columns,
    state: { globalFilter },
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Manajemen Pemberkasan</h1>
          <p className="text-sm text-slate-500">Penyimpanan dokumen penting dan arsip</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Upload Widget */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6 h-full flex flex-col justify-center items-center text-center relative overflow-hidden group">
            <input 
              type="file" 
              onChange={handleFileUpload} 
              disabled={isUploading}
              className="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed z-10" 
            />
            {isUploading ? (
              <div className="animate-pulse flex flex-col items-center">
                <UploadCloud className="text-primary mb-4" size={48} />
                <h3 className="font-bold text-slate-700">Mengunggah...</h3>
                <p className="text-sm text-slate-500 mt-1">Harap tunggu sebentar</p>
              </div>
            ) : (
              <div className="flex flex-col items-center transition-transform group-hover:scale-105">
                <div className="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-4">
                  <UploadCloud size={32} />
                </div>
                <h3 className="font-bold text-slate-700">Tarik & Lepas Berkas</h3>
                <p className="text-sm text-slate-500 mt-1">atau klik untuk memilih file (PDF, JPG, PNG)</p>
                <div className="mt-6 px-6 py-2 bg-slate-100 text-slate-600 rounded-full text-sm font-medium">
                  Upload Berkas
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Files List */}
        <div className="lg:col-span-2">
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col">
            <div className="p-4 border-b border-slate-200 flex items-center justify-between gap-4 bg-slate-50">
              <div className="relative w-full">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                <input
                  value={globalFilter ?? ''}
                  onChange={e => setGlobalFilter(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                  placeholder="Cari nama berkas..."
                />
              </div>
            </div>

            <div className="overflow-x-auto flex-1">
              <table className="w-full text-sm text-left text-slate-600">
                <thead className="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                  {table.getHeaderGroups().map(headerGroup => (
                    <tr key={headerGroup.id}>
                      {headerGroup.headers.map(header => (
                        <th key={header.id} className="px-4 py-3 font-semibold whitespace-nowrap">
                          {flexRender(header.column.columnDef.header, header.getContext())}
                        </th>
                      ))}
                    </tr>
                  ))}
                </thead>
                <tbody>
                  {loading ? (
                    <tr><td colSpan={5} className="px-4 py-8 text-center text-slate-500 animate-pulse">Memuat berkas...</td></tr>
                  ) : table.getRowModel().rows.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="px-4 py-12 text-center text-slate-500">
                        <FileText size={48} className="mx-auto text-slate-300 mb-3" />
                        <p>Belum ada berkas tersimpan</p>
                      </td>
                    </tr>
                  ) : (
                    table.getRowModel().rows.map(row => (
                      <tr key={row.id} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        {row.getVisibleCells().map(cell => (
                          <td key={cell.id} className="px-4 py-2 whitespace-nowrap">
                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                          </td>
                        ))}
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
            
            <div className="p-4 border-t border-slate-200 flex items-center justify-between text-sm text-slate-500 bg-white">
              <div>
                Halaman {table.getState().pagination.pageIndex + 1} dari {table.getPageCount() || 1}
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => table.previousPage()}
                  disabled={!table.getCanPreviousPage()}
                  className="px-3 py-1 border border-slate-300 rounded-md hover:bg-slate-50 disabled:opacity-50"
                >
                  Sebelumnnya
                </button>
                <button
                  onClick={() => table.nextPage()}
                  disabled={!table.getCanNextPage()}
                  className="px-3 py-1 border border-slate-300 rounded-md hover:bg-slate-50 disabled:opacity-50"
                >
                  Selanjutnya
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Pemberkasan;
