import { useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { 
  useReactTable, 
  getCoreRowModel, 
  getFilteredRowModel, 
  getPaginationRowModel, 
  getSortedRowModel,
  flexRender 
} from '@tanstack/react-table';
import { Search, Plus, Download, Edit, Trash2, Power, PowerOff } from 'lucide-react';
import SantriFormModal from '../components/SantriFormModal';

const MasterData = () => {
  const [data, setData] = useState([]);
  const [filters, setFilters] = useState({});
  const [loading, setLoading] = useState(true);
  const [globalFilter, setGlobalFilter] = useState('');
  const [rowSelection, setRowSelection] = useState({});
  
  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editKds, setEditKds] = useState(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const res = await axios.get('/api/master-data');
      if (res.data.success) {
        setData(res.data.data.santris);
        setFilters(res.data.data.filters);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const toggleAktif = async (kds) => {
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const res = await axios.post(`/api/santri/${kds}/toggle-aktif`, {}, { headers: { 'X-CSRF-Token': csrf }});
      if (res.data.success) fetchData();
    } catch (err) {
      console.error(err);
    }
  };

  const openAddModal = () => {
    setEditKds(null);
    setIsModalOpen(true);
  };

  const openEditModal = (kds) => {
    setEditKds(kds);
    setIsModalOpen(true);
  };

  const columns = useMemo(() => [
    {
      id: 'select',
      header: ({ table }) => (
        <input
          type="checkbox"
          className="rounded border-slate-300 text-primary focus:ring-primary"
          checked={table.getIsAllRowsSelected()}
          onChange={table.getToggleAllRowsSelectedHandler()}
        />
      ),
      cell: ({ row }) => (
        <input
          type="checkbox"
          className="rounded border-slate-300 text-primary focus:ring-primary"
          checked={row.getIsSelected()}
          onChange={row.getToggleSelectedHandler()}
        />
      ),
    },
    { accessorKey: 'no_paspor', header: 'No Paspor' },
    { accessorKey: 'nama', header: 'Nama Santri' },
    { accessorKey: 'kelas', header: 'Kelas' },
    { accessorKey: 'daerah', header: 'Daerah' },
    { accessorKey: 'rayon', header: 'Rayon' },
    { accessorKey: 'exp_paspor', header: 'Exp Paspor' },
    { accessorKey: 'exp_itas', header: 'Exp ITAS' },
    { accessorKey: 'pondok', header: 'Pondok', enableHiding: true },
    { accessorKey: 'kepengurusan', header: 'Kepengurusan', enableHiding: true },
    { accessorKey: 'negara', header: 'Negara', enableHiding: true },
    {
      id: 'actions',
      header: 'Aksi',
      cell: ({ row }) => (
        <div className="flex items-center gap-2">
          <button onClick={() => openEditModal(row.original.kds)} className="text-primary hover:text-primary/80"><Edit size={16} /></button>
          <button onClick={() => toggleAktif(row.original.kds)} className="text-danger hover:text-danger/80">
            {row.original.aktif ? <PowerOff size={16} /> : <Power size={16} />}
          </button>
        </div>
      )
    }
  ], []);

  const table = useReactTable({
    data,
    columns,
    state: { globalFilter, rowSelection, columnVisibility: { pondok: false, kepengurusan: false, negara: false } },
    enableRowSelection: true,
    onRowSelectionChange: setRowSelection,
    onGlobalFilterChange: setGlobalFilter,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
  });

  const handleBulkToggle = async () => {
    const selectedKds = table.getSelectedRowModel().rows.map(row => row.original.kds);
    if (selectedKds.length === 0) return;
    
    if (!confirm(`Yakin ingin menonaktifkan ${selectedKds.length} santri terpilih secara massal?`)) return;
    
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const res = await axios.post('/api/santri/bulk-toggle', { kds: selectedKds }, { headers: { 'X-CSRF-Token': csrf }});
      if (res.data.success) {
        setRowSelection({});
        fetchData();
      }
    } catch (err) {
      console.error(err);
    }
  };

  const handleExport = () => {
    // Navigate to Yii3 export endpoint for now to reuse powerful python export
    window.location.href = '/master-data?export=excel';
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Master Data Santri</h1>
          <p className="text-sm text-slate-500">Kelola seluruh data paspor dan ITAS santri</p>
        </div>
        <div className="flex gap-2">
          {Object.keys(rowSelection).length > 0 && (
            <button onClick={handleBulkToggle} className="flex items-center gap-2 bg-danger text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-danger/90 transition-colors">
              <PowerOff size={18} /> Nonaktifkan Terpilih ({Object.keys(rowSelection).length})
            </button>
          )}
          <button onClick={handleExport} className="flex items-center gap-2 bg-success text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-success/90 transition-colors">
            <Download size={18} /> Export Excel
          </button>
          <button onClick={openAddModal} className="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
            <Plus size={18} /> Tambah Santri
          </button>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label className="block text-xs font-medium text-slate-500 mb-1">Pondok</label>
          <select className="w-full border-slate-300 rounded-md text-sm p-2 bg-slate-50 border outline-none focus:border-primary" onChange={e => table.getColumn('pondok')?.setFilterValue(e.target.value)}>
            <option value="">Semua Pondok</option>
            {filters?.pondok?.map((v, i) => <option key={i} value={v}>{v}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-xs font-medium text-slate-500 mb-1">Kepengurusan</label>
          <select className="w-full border-slate-300 rounded-md text-sm p-2 bg-slate-50 border outline-none focus:border-primary" onChange={e => table.getColumn('kepengurusan')?.setFilterValue(e.target.value)}>
            <option value="">Semua Kepengurusan</option>
            {filters?.kepengurusan?.map((v, i) => <option key={i} value={v}>{v}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-xs font-medium text-slate-500 mb-1">Negara Asal</label>
          <select className="w-full border-slate-300 rounded-md text-sm p-2 bg-slate-50 border outline-none focus:border-primary" onChange={e => table.getColumn('negara')?.setFilterValue(e.target.value)}>
            <option value="">Semua Negara</option>
            {filters?.negara?.map((v, i) => <option key={i} value={v}>{v}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-xs font-medium text-slate-500 mb-1">Kelas</label>
          <select className="w-full border-slate-300 rounded-md text-sm p-2 bg-slate-50 border outline-none focus:border-primary" onChange={e => table.getColumn('kelas')?.setFilterValue(e.target.value)}>
            <option value="">Semua Kelas</option>
            {filters?.kelas?.map((v, i) => <option key={i} value={v}>{v}</option>)}
          </select>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div className="p-4 border-b border-slate-200 flex items-center justify-between gap-4 bg-slate-50">
          <div className="relative w-full max-w-sm">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
            <input
              value={globalFilter ?? ''}
              onChange={e => setGlobalFilter(e.target.value)}
              className="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
              placeholder="Cari nama, stambuk, atau paspor..."
            />
          </div>
          <div className="flex items-center gap-2 text-sm text-slate-600">
            {Object.keys(rowSelection).length > 0 && (
              <span className="bg-primary/10 text-primary px-3 py-1 rounded-full font-medium">
                {Object.keys(rowSelection).length} Terpilih
              </span>
            )}
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-slate-600">
            <thead className="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
              {table.getHeaderGroups().map(headerGroup => (
                <tr key={headerGroup.id}>
                  {headerGroup.headers.map(header => (
                    <th key={header.id} className="px-4 py-3 font-semibold whitespace-nowrap">
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </th>
                  ))}
                </tr>
              ))}
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan={columns.length} className="px-4 py-8 text-center text-slate-500 animate-pulse">Memuat data...</td></tr>
              ) : table.getRowModel().rows.length === 0 ? (
                <tr><td colSpan={columns.length} className="px-4 py-8 text-center text-slate-500">Tidak ada data ditemukan</td></tr>
              ) : (
                table.getRowModel().rows.map(row => (
                  <tr key={row.id} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    {row.getVisibleCells().map(cell => (
                      <td key={cell.id} className="px-4 py-3 whitespace-nowrap">
                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                      </td>
                    ))}
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="p-4 border-t border-slate-200 flex items-center justify-between text-sm text-slate-500">
          <div>
            Menampilkan {table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1} s/d{' '}
            {Math.min((table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize, table.getPreFilteredRowModel().rows.length)} dari{' '}
            {table.getPreFilteredRowModel().rows.length} data
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

      <SantriFormModal 
        isOpen={isModalOpen} 
        onClose={() => setIsModalOpen(false)} 
        kds={editKds} 
        onSuccess={fetchData} 
      />
    </div>
  );
};

export default MasterData;
