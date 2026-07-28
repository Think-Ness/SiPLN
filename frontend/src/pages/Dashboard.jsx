import { useEffect, useState } from 'react';
import axios from 'axios';
import { Users, UserX, FileWarning, AlertCircle } from 'lucide-react';
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

const Dashboard = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [status, setStatus] = useState('1'); // 1: Aktif, 0: Inaktif, all: Semua

  useEffect(() => {
    setLoading(true);
    axios.get(`/api/dashboard?status=${status}`)
      .then(res => {
        setData(res.data.data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [status]);

  if (loading && !data) return <div className="flex h-64 items-center justify-center text-primary font-medium animate-pulse">Memuat Data Dashboard...</div>;

  // Process data for charts
  const negaraCount = {};
  const angkatanCount = {};
  const pondokCount = {};
  const kepengurusanCount = {};

  (data?.santris || []).forEach(s => {
    if (s.negara) negaraCount[s.negara] = (negaraCount[s.negara] || 0) + 1;
    if (s.kelas) angkatanCount[s.kelas] = (angkatanCount[s.kelas] || 0) + 1;
    if (s.pondok) pondokCount[s.pondok] = (pondokCount[s.pondok] || 0) + 1;
    if (s.kepengurusan) kepengurusanCount[s.kepengurusan] = (kepengurusanCount[s.kepengurusan] || 0) + 1;
  });

  const toChartData = (obj) => Object.entries(obj).map(([name, value]) => ({ name, value })).sort((a, b) => b.value - a.value);

  const negaraData = toChartData(negaraCount);
  const angkatanData = toChartData(angkatanCount);
  const pondokData = toChartData(pondokCount);
  const kepengurusanData = toChartData(kepengurusanCount);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Dashboard Analitik</h1>
          <p className="text-slate-500 text-sm">Visualisasi data santri terintegrasi (React v2.0)</p>
        </div>
        <div className="flex bg-white rounded-lg p-1 shadow-sm border border-slate-200">
          {[
            { id: '1', label: 'Aktif' },
            { id: '0', label: 'Inaktif' },
            { id: 'all', label: 'Semua' },
          ].map(opt => (
            <button
              key={opt.id}
              onClick={() => setStatus(opt.id)}
              className={`px-4 py-1.5 text-sm font-medium rounded-md transition-all ${
                status === opt.id 
                  ? 'bg-primary text-white shadow-sm' 
                  : 'text-slate-600 hover:bg-slate-50'
              }`}
            >
              {opt.label}
            </button>
          ))}
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
          <div className="w-12 h-12 rounded-full bg-success/10 text-success flex items-center justify-center">
            <Users size={24} />
          </div>
          <div>
            <div className="text-2xl font-bold text-slate-800">{data?.aktif || 0}</div>
            <div className="text-slate-500 text-sm font-medium">Santri Aktif</div>
          </div>
        </div>
        <div className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
          <div className="w-12 h-12 rounded-full bg-danger/10 text-danger flex items-center justify-center">
            <UserX size={24} />
          </div>
          <div>
            <div className="text-2xl font-bold text-slate-800">{data?.inaktif || 0}</div>
            <div className="text-slate-500 text-sm font-medium">Santri Inaktif</div>
          </div>
        </div>
        <div className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
          <div className="w-12 h-12 rounded-full bg-warning/10 text-warning flex items-center justify-center">
            <FileWarning size={24} />
          </div>
          <div>
            <div className="text-2xl font-bold text-slate-800">{data?.expPasporSoon || 0}</div>
            <div className="text-slate-500 text-sm font-medium">Paspor Exp &le; 1 Bln</div>
          </div>
        </div>
        <div className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
          <div className="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center">
            <AlertCircle size={24} />
          </div>
          <div>
            <div className="text-2xl font-bold text-slate-800">{data?.expItasSoon || 0}</div>
            <div className="text-slate-500 text-sm font-medium">ITAS Exp &le; 3 Bln</div>
          </div>
        </div>
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Negara */}
        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
          <h3 className="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span className="w-2 h-6 bg-primary rounded-full"></span> Distribusi Negara Asal
          </h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={negaraData} innerRadius={60} outerRadius={100} paddingAngle={2} dataKey="value" nameKey="name">
                  {negaraData.map((entry, index) => <Cell key={index} fill={COLORS[index % COLORS.length]} />)}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Pondok */}
        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
          <h3 className="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span className="w-2 h-6 bg-success rounded-full"></span> Distribusi Pondok
          </h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={pondokData} innerRadius={0} outerRadius={100} dataKey="value" nameKey="name">
                  {pondokData.map((entry, index) => <Cell key={index} fill={COLORS[(index+2) % COLORS.length]} />)}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Kepengurusan */}
        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-100 lg:col-span-2">
          <h3 className="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span className="w-2 h-6 bg-warning rounded-full"></span> Distribusi Kepengurusan
          </h3>
          <div className="h-72">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={kepengurusanData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} />
                <YAxis axisLine={false} tickLine={false} />
                <Tooltip cursor={{fill: '#f8fafc'}} contentStyle={{borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'}} />
                <Bar dataKey="value" fill="#3b82f6" radius={[4, 4, 0, 0]} barSize={40} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
