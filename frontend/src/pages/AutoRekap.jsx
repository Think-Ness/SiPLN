import { useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { 
  useReactTable, 
  getCoreRowModel, 
  getFilteredRowModel, 
  flexRender 
} from '@tanstack/react-table';
import { FileWarning, AlertCircle, Search, CalendarDays } from 'lucide-react';

const AutoRekap = () => {
  const [itasData, setItasData] = useState([]);
  const [pasporData, setPasporData] = useState([]);
  const [kepList, setKepList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [globalFilter, setGlobalFilter] = useState('');

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const res = await axios.get('/api/auto-rekap');
      if (res.data.success) {
        setItasData(res.data.data.itas);
        setPasporData(res.data.data.paspor);
        setKepList(res.data.data.kepList);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const pasporColumns = useMemo(() => [
    { accessorKey: 'no_paspor', header: 'No Paspor' },
    { accessorKey: 'nama', header: 'Nama Santri' },
    { accessorKey: 'kelas', header: 'Kelas' },
    { accessorKey: 'daerah', header: 'Daerah' },
    { accessorKey: 'kepengurusan', header: 'Kepengurusan' },
    { accessorKey: 'exp_paspor', header: 'Exp Paspor' },
  ], []);

  const itasColumns = useMemo(() => [
    { accessorKey: 'no_paspor', header: 'No Paspor' },
    { accessorKey: 'nama', header: 'Nama Santri' },
    { accessorKey: 'kelas', header: 'Kelas' },
    { accessorKey: 'daerah', header: 'Daerah' },
    { accessorKey: 'kepengurusan', header: 'Kepengurusan' },
    { accessorKey: 'exp_itas', header: 'Exp ITAS' },
  ], []);

  const tablePaspor = useReactTable({
    data: pasporData,
    columns: pasporColumns,
    state: { globalFilter },
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
  });

  const tableItas = useReactTable({
    data: itasData,
    columns: itasColumns,
    state: { globalFilter },
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
  });

  const renderTable = (tableInstance, title, icon, colorClass, bgClass) => (
    <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
      <div className={`p-4 border-b border-slate-200 flex items-center gap-3 ${bgClass}`}>
        <div className={`w-10 h-10 rounded-full flex items-center justify-center bg-white ${colorClass}`}>
          {icon}
        </div>
        <div>
          <h2 className="font-bold text-slate-800">{title}</h2>
          <p className="text-xs text-slate-600 font-medium">{tableInstance.getPreFilteredRowModel().rows.length} Data ditemukan</p>
        </div>
      </div>
      <div className="overflow-x-auto flex-1">
        <table className="w-full text-sm text-left text-slate-600">
          <thead className="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
            {tableInstance.getHeaderGroups().map(headerGroup => (
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
              <tr><td colSpan={6} className="px-4 py-8 text-center text-slate-500 animate-pulse">Memuat data...</td></tr>
            ) : tableInstance.getRowModel().rows.length === 0 ? (
              <tr><td colSpan={6} className="px-4 py-8 text-center text-slate-500">Tidak ada data</td></tr>
            ) : (
              tableInstance.getRowModel().rows.map(row => (
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
    </div>
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Auto Rekap</h1>
          <p className="text-sm text-slate-500">Pantau paspor (18 bln) dan ITAS (3 bln) yang akan expired</p>
        </div>
        <Link
          to="/kalender-expiry"
          className="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white font-semibold text-sm rounded-xl shadow-sm hover:bg-primary/90 transition-all hover:shadow-md self-start"
        >
          <CalendarDays size={18} />
          Lihat Kalender
        </Link>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col md:flex-row gap-4 items-center">
        <div className="relative w-full md:w-96">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
          <input
            value={globalFilter ?? ''}
            onChange={e => setGlobalFilter(e.target.value)}
            className="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
            placeholder="Cari nama, stambuk, atau paspor..."
          />
        </div>
        <div className="w-full md:w-64">
          <select 
            className="w-full border-slate-300 rounded-lg text-sm p-2 bg-slate-50 border outline-none focus:border-primary"
            onChange={e => {
              tablePaspor.getColumn('kepengurusan')?.setFilterValue(e.target.value);
              tableItas.getColumn('kepengurusan')?.setFilterValue(e.target.value);
            }}
          >
            <option value="">Semua Kepengurusan</option>
            {kepList.map((v, i) => <option key={i} value={v}>{v}</option>)}
          </select>
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {renderTable(tablePaspor, "Paspor (<= 18 Bulan)", <FileWarning size={20} />, "text-warning", "bg-warning/10")}
        {renderTable(tableItas, "ITAS (<= 3 Bulan)", <AlertCircle size={20} />, "text-danger", "bg-danger/10")}
      </div>
    </div>
  );
};

export default AutoRekap;
