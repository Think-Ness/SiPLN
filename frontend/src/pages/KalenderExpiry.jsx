import { useEffect, useState, useMemo, useCallback } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import {
  CalendarDays, ChevronLeft, ChevronRight, X, Filter,
  AlertCircle, FileWarning, Clock, AlertTriangle,
  ArrowLeft, Loader2, Users, CalendarClock
} from 'lucide-react';

// ─── Color config for event types ───────────────────────────────
const EVENT_CONFIG = {
  itas_start: {
    bg: 'bg-amber-500',
    bgLight: 'bg-amber-50',
    border: 'border-amber-200',
    text: 'text-amber-700',
    dot: 'bg-amber-500',
    label: 'Mulai Proses ITAS',
    tagLabel: 'ITAS Proses',
    icon: Clock,
  },
  itas_exp: {
    bg: 'bg-blue-500',
    bgLight: 'bg-blue-50',
    border: 'border-blue-200',
    text: 'text-blue-700',
    dot: 'bg-blue-500',
    label: 'Expiry ITAS',
    tagLabel: 'ITAS Exp',
    icon: AlertCircle,
  },
  paspor_start: {
    bg: 'bg-violet-500',
    bgLight: 'bg-violet-50',
    border: 'border-violet-200',
    text: 'text-violet-700',
    dot: 'bg-violet-500',
    label: 'Mulai Proses Paspor',
    tagLabel: 'Paspor Proses',
    icon: CalendarClock,
  },
  paspor_exp: {
    bg: 'bg-rose-500',
    bgLight: 'bg-rose-50',
    border: 'border-rose-200',
    text: 'text-rose-700',
    dot: 'bg-rose-500',
    label: 'Expiry Paspor',
    tagLabel: 'Paspor Exp',
    icon: FileWarning,
  },
};

const DAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
const MONTHS = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// ─── Helpers ────────────────────────────────────────────────────
const pad = (n) => String(n).padStart(2, '0');

const getCalendarDays = (year, month) => {
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  // Monday = 0, Sunday = 6
  let startDow = firstDay.getDay() - 1;
  if (startDow < 0) startDow = 6;

  const days = [];
  // Previous month fill
  const prevLast = new Date(year, month, 0).getDate();
  for (let i = startDow - 1; i >= 0; i--) {
    days.push({
      date: `${year}-${pad(month)}-${pad(prevLast - i)}`,
      day: prevLast - i,
      isCurrentMonth: false,
    });
  }
  // Current month
  for (let d = 1; d <= lastDay.getDate(); d++) {
    days.push({
      date: `${year}-${pad(month + 1)}-${pad(d)}`,
      day: d,
      isCurrentMonth: true,
    });
  }
  // Next month fill
  const remaining = 42 - days.length;
  for (let d = 1; d <= remaining; d++) {
    days.push({
      date: `${year}-${pad(month + 2 > 12 ? 1 : month + 2)}-${pad(d)}`,
      day: d,
      isCurrentMonth: false,
    });
  }
  return days;
};

const formatDateID = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
};

const daysDiff = (dateStr) => {
  const now = new Date();
  now.setHours(0, 0, 0, 0);
  const target = new Date(dateStr);
  target.setHours(0, 0, 0, 0);
  return Math.ceil((target - now) / (1000 * 60 * 60 * 24));
};

// ─── Stats Card ─────────────────────────────────────────────────
const StatCard = ({ icon: Icon, label, value, color, subtext }) => (
  <div className={`rounded-xl p-4 border ${color} backdrop-blur-sm transition-all hover:scale-[1.02] hover:shadow-md`}>
    <div className="flex items-center gap-3">
      <div className={`w-10 h-10 rounded-lg flex items-center justify-center bg-white/80 shadow-sm`}>
        <Icon size={20} />
      </div>
      <div className="min-w-0">
        <div className="text-2xl font-bold">{value}</div>
        <div className="text-xs font-medium opacity-75 truncate">{label}</div>
        {subtext && <div className="text-[10px] opacity-60 mt-0.5">{subtext}</div>}
      </div>
    </div>
  </div>
);

// ─── Event Pill (inside calendar cell) ──────────────────────────
const EventPill = ({ event, onClick }) => {
  const cfg = EVENT_CONFIG[event.type];
  if (!cfg) return null;
  return (
    <button
      onClick={(e) => { e.stopPropagation(); onClick(event); }}
      className={`w-full text-left px-1.5 py-0.5 rounded text-[10px] font-medium truncate transition-all
        hover:brightness-110 hover:shadow-sm cursor-pointer
        ${event.is_expired ? 'bg-red-100 text-red-700 line-through opacity-75' : `${cfg.bgLight} ${cfg.text}`}`}
      title={event.label}
    >
      <span className={`inline-block w-1.5 h-1.5 rounded-full mr-1 ${event.is_expired ? 'bg-red-500' : cfg.dot}`}></span>
      {event.santri.nama.split(' ')[0]}
    </button>
  );
};

// ─── More Events Popover ────────────────────────────────────────
const MoreEventsButton = ({ count, events, onEventClick }) => {
  const [open, setOpen] = useState(false);
  return (
    <div className="relative">
      <button
        onClick={(e) => { e.stopPropagation(); setOpen(!open); }}
        className="w-full text-center text-[10px] font-semibold text-primary hover:text-primary/80 cursor-pointer py-0.5"
      >
        +{count} lainnya
      </button>
      {open && (
        <>
          <div className="fixed inset-0 z-30" onClick={() => setOpen(false)} />
          <div className="absolute z-40 left-0 top-full mt-1 w-56 bg-white rounded-xl shadow-xl border border-slate-200 p-2 space-y-1 max-h-48 overflow-y-auto">
            {events.map((ev) => (
              <EventPill key={ev.id} event={ev} onClick={(e) => { setOpen(false); onEventClick(e); }} />
            ))}
          </div>
        </>
      )}
    </div>
  );
};

// ─── Detail Modal ───────────────────────────────────────────────
const DetailModal = ({ event, onClose }) => {
  if (!event) return null;
  const cfg = EVENT_CONFIG[event.type];
  const Icon = cfg?.icon || AlertCircle;
  const diff = daysDiff(event.exp_date);
  const diffStart = daysDiff(event.start_date);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onClick={onClose}>
      <div
        className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in"
        onClick={(e) => e.stopPropagation()}
        style={{ animation: 'modalIn 0.25s ease-out' }}
      >
        {/* Header */}
        <div className={`px-6 py-4 ${cfg?.bgLight || 'bg-slate-50'} border-b ${cfg?.border || 'border-slate-200'}`}>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${cfg?.bg || 'bg-slate-500'} text-white shadow-lg`}>
                <Icon size={20} />
              </div>
              <div>
                <h3 className="font-bold text-slate-800 text-lg">{event.santri.nama}</h3>
                <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${cfg?.bg || 'bg-slate-500'} text-white`}>
                  {cfg?.tagLabel || event.type}
                </span>
              </div>
            </div>
            <button onClick={onClose} className="p-2 rounded-lg hover:bg-white/60 text-slate-500 transition-colors">
              <X size={20} />
            </button>
          </div>
        </div>

        {/* Body */}
        <div className="px-6 py-5 space-y-4">
          {/* Status Badge */}
          <div className={`rounded-xl p-3 text-center font-semibold text-sm ${
            event.is_expired
              ? 'bg-red-50 text-red-700 border border-red-200'
              : diff <= 30
                ? 'bg-amber-50 text-amber-700 border border-amber-200'
                : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
          }`}>
            {event.is_expired
              ? `⚠️ Sudah expired ${Math.abs(diff)} hari yang lalu`
              : diff <= 0
                ? '⚠️ Expired hari ini!'
                : diff <= 30
                  ? `⏳ ${diff} hari lagi menuju expiry`
                  : `✅ Masih ${diff} hari menuju expiry`
            }
          </div>

          {/* Info Grid */}
          <div className="grid grid-cols-2 gap-3">
            <InfoItem label="No. Paspor" value={event.no_paspor || '-'} />
            <InfoItem label="Kelas" value={event.santri.kelas || '-'} />
            <InfoItem label="Daerah" value={event.santri.daerah || '-'} />
            <InfoItem label="Kepengurusan" value={event.santri.kepengurusan || '-'} />
          </div>

          {/* Timeline */}
          <div className="bg-slate-50 rounded-xl p-4 space-y-3">
            <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wide">Timeline Proses</h4>
            <div className="flex items-start gap-3">
              <div className="flex flex-col items-center">
                <div className={`w-3 h-3 rounded-full ${diffStart <= 0 ? 'bg-emerald-500' : 'bg-slate-300'} ring-4 ${diffStart <= 0 ? 'ring-emerald-100' : 'ring-slate-100'}`} />
                <div className="w-0.5 h-8 bg-slate-200" />
              </div>
              <div>
                <div className="text-sm font-semibold text-slate-800">Mulai Pengerjaan</div>
                <div className="text-xs text-slate-500">{formatDateID(event.start_date)}</div>
                <div className="text-[10px] text-slate-400 mt-0.5">
                  {event.type.includes('itas') ? '3 bulan sebelum expiry' : '18 bulan sebelum expiry'}
                </div>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <div className="flex flex-col items-center">
                <div className={`w-3 h-3 rounded-full ${event.is_expired ? 'bg-red-500 ring-red-100' : 'bg-slate-300 ring-slate-100'} ring-4`} />
              </div>
              <div>
                <div className="text-sm font-semibold text-slate-800">Tanggal Expiry</div>
                <div className="text-xs text-slate-500">{formatDateID(event.exp_date)}</div>
                {event.is_expired && <div className="text-[10px] text-red-500 font-medium mt-0.5">Sudah expired!</div>}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const InfoItem = ({ label, value }) => (
  <div className="bg-slate-50 rounded-lg px-3 py-2">
    <div className="text-[10px] text-slate-400 font-medium uppercase tracking-wide">{label}</div>
    <div className="text-sm font-semibold text-slate-700 truncate">{value}</div>
  </div>
);

// ═════════════════════════════════════════════════════════════════
// MAIN COMPONENT
// ═════════════════════════════════════════════════════════════════
const KalenderExpiry = () => {
  const navigate = useNavigate();
  const today = new Date();
  const [currentMonth, setCurrentMonth] = useState(today.getMonth());
  const [currentYear, setCurrentYear] = useState(today.getFullYear());
  const [events, setEvents] = useState([]);
  const [stats, setStats] = useState(null);
  const [kepList, setKepList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const [filterKep, setFilterKep] = useState('');
  const [filterType, setFilterType] = useState('all'); // all, itas, paspor
  const [showUpcoming, setShowUpcoming] = useState(true);

  const todayStr = useMemo(() => {
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }, []);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (filterKep) params.set('kepengurusan', filterKep);
      const res = await axios.get(`/api/auto-rekap/calendar?${params.toString()}`);
      if (res.data.success) {
        setEvents(res.data.data.events);
        setStats(res.data.data.stats);
        setKepList(res.data.data.kepList);
      }
    } catch (err) {
      console.error('Failed to fetch calendar data:', err);
    } finally {
      setLoading(false);
    }
  }, [filterKep]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  // Filter events by type
  const filteredEvents = useMemo(() => {
    if (filterType === 'all') return events;
    return events.filter((e) => e.type.startsWith(filterType));
  }, [events, filterType]);

  // Group events by date
  const eventsByDate = useMemo(() => {
    const map = {};
    filteredEvents.forEach((ev) => {
      if (!map[ev.date]) map[ev.date] = [];
      map[ev.date].push(ev);
    });
    return map;
  }, [filteredEvents]);

  // Calendar grid days
  const calendarDays = useMemo(
    () => getCalendarDays(currentYear, currentMonth),
    [currentYear, currentMonth]
  );

  // Upcoming events (next 30 days)
  const upcomingEvents = useMemo(() => {
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const end = new Date(now);
    end.setDate(end.getDate() + 60);
    return filteredEvents
      .filter((ev) => {
        const d = new Date(ev.date);
        return d >= now && d <= end;
      })
      .sort((a, b) => new Date(a.date) - new Date(b.date))
      .slice(0, 20);
  }, [filteredEvents]);

  const goToday = () => {
    setCurrentMonth(today.getMonth());
    setCurrentYear(today.getFullYear());
  };

  const goPrev = () => {
    if (currentMonth === 0) {
      setCurrentMonth(11);
      setCurrentYear(currentYear - 1);
    } else {
      setCurrentMonth(currentMonth - 1);
    }
  };

  const goNext = () => {
    if (currentMonth === 11) {
      setCurrentMonth(0);
      setCurrentYear(currentYear + 1);
    } else {
      setCurrentMonth(currentMonth + 1);
    }
  };

  return (
    <div className="space-y-5">
      {/* Modal animation style */}
      <style>{`
        @keyframes modalIn {
          from { opacity: 0; transform: scale(0.95) translateY(10px); }
          to { opacity: 1; transform: scale(1) translateY(0); }
        }
      `}</style>

      {/* ─── Header ──────────────────────────────────────────── */}
      <div className="flex flex-col sm:flex-row justify-between gap-4">
        <div className="flex items-center gap-3">
          <button
            onClick={() => navigate('/auto-rekap')}
            className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors"
            title="Kembali ke Auto Rekap"
          >
            <ArrowLeft size={20} />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-slate-800 flex items-center gap-2">
              <CalendarDays className="text-primary" size={28} />
              Kalender Expiry
            </h1>
            <p className="text-sm text-slate-500">
              Tracking jadwal perpanjangan ITAS & Paspor santri
            </p>
          </div>
        </div>
      </div>

      {/* ─── Stats Cards ────────────────────────────────────── */}
      {stats && (
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <StatCard
            icon={Clock}
            label="Proses ITAS Bulan Ini"
            value={stats.itasProcessThisMonth}
            color="bg-amber-50 border-amber-200 text-amber-700"
            subtext="Mulai pengerjaan"
          />
          <StatCard
            icon={CalendarClock}
            label="Proses Paspor Bulan Ini"
            value={stats.pasporProcessThisMonth}
            color="bg-violet-50 border-violet-200 text-violet-700"
            subtext="Mulai pengerjaan"
          />
          <StatCard
            icon={AlertTriangle}
            label="ITAS Sudah Expired"
            value={stats.itasExpiredTotal}
            color="bg-red-50 border-red-200 text-red-700"
            subtext="Perlu tindakan segera"
          />
          <StatCard
            icon={FileWarning}
            label="Paspor Sudah Expired"
            value={stats.pasporExpiredTotal}
            color="bg-rose-50 border-rose-200 text-rose-700"
            subtext="Perlu tindakan segera"
          />
        </div>
      )}

      {/* ─── Filter Bar ─────────────────────────────────────── */}
      <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-3 flex flex-col md:flex-row gap-3 items-center">
        <div className="flex items-center gap-2 text-slate-500">
          <Filter size={16} />
          <span className="text-sm font-medium">Filter:</span>
        </div>
        <div className="flex flex-wrap gap-2 flex-1">
          {/* Type Filter */}
          <div className="flex bg-slate-100 rounded-lg p-0.5">
            {[
              { id: 'all', label: 'Semua' },
              { id: 'itas', label: 'ITAS' },
              { id: 'paspor', label: 'Paspor' },
            ].map((opt) => (
              <button
                key={opt.id}
                onClick={() => setFilterType(opt.id)}
                className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-all ${
                  filterType === opt.id
                    ? 'bg-white text-primary shadow-sm'
                    : 'text-slate-500 hover:text-slate-700'
                }`}
              >
                {opt.label}
              </button>
            ))}
          </div>

          {/* Kepengurusan Filter */}
          <select
            className="border border-slate-200 rounded-lg text-xs px-3 py-1.5 bg-white outline-none focus:border-primary transition-colors min-w-[160px]"
            value={filterKep}
            onChange={(e) => setFilterKep(e.target.value)}
          >
            <option value="">Semua Kepengurusan</option>
            {kepList.map((v, i) => (
              <option key={i} value={v}>{v}</option>
            ))}
          </select>
        </div>

        {/* Toggle upcoming panel */}
        <button
          onClick={() => setShowUpcoming(!showUpcoming)}
          className={`hidden lg:flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
            showUpcoming
              ? 'bg-primary/10 text-primary'
              : 'bg-slate-100 text-slate-500 hover:text-slate-700'
          }`}
        >
          <CalendarClock size={14} />
          Upcoming
        </button>
      </div>

      {/* ─── Calendar + Upcoming Panel ──────────────────────── */}
      <div className="flex gap-5">
        {/* Calendar */}
        <div className="flex-1 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          {/* Month Nav */}
          <div className="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
            <h2 className="text-lg font-bold text-slate-800">
              {MONTHS[currentMonth]} {currentYear}
            </h2>
            <div className="flex items-center gap-1">
              <button
                onClick={goToday}
                className="px-3 py-1 text-xs font-semibold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors mr-2"
              >
                Hari Ini
              </button>
              <button onClick={goPrev} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors">
                <ChevronLeft size={18} />
              </button>
              <button onClick={goNext} className="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors">
                <ChevronRight size={18} />
              </button>
            </div>
          </div>

          {loading ? (
            <div className="flex items-center justify-center h-96 text-primary">
              <Loader2 size={32} className="animate-spin" />
              <span className="ml-3 font-medium">Memuat kalender...</span>
            </div>
          ) : (
            <div className="p-2">
              {/* Day headers */}
              <div className="grid grid-cols-7 mb-1">
                {DAYS.map((d) => (
                  <div key={d} className="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider py-2">
                    {d}
                  </div>
                ))}
              </div>

              {/* Calendar Grid */}
              <div className="grid grid-cols-7 gap-px bg-slate-100 rounded-lg overflow-hidden">
                {calendarDays.map((cell, idx) => {
                  const dateEvents = eventsByDate[cell.date] || [];
                  const isToday = cell.date === todayStr;
                  const maxShow = 3;
                  const visibleEvents = dateEvents.slice(0, maxShow);
                  const extraEvents = dateEvents.slice(maxShow);

                  return (
                    <div
                      key={idx}
                      className={`min-h-[90px] p-1 bg-white transition-colors relative group
                        ${!cell.isCurrentMonth ? 'opacity-40' : ''}
                        ${isToday ? 'ring-2 ring-primary ring-inset' : ''}
                      `}
                    >
                      {/* Day number */}
                      <div className="flex items-center justify-between mb-0.5">
                        <span className={`text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full
                          ${isToday ? 'bg-primary text-white' : 'text-slate-600'}
                        `}>
                          {cell.day}
                        </span>
                        {dateEvents.length > 0 && (
                          <span className="text-[9px] font-bold text-slate-400 bg-slate-100 rounded px-1">
                            {dateEvents.length}
                          </span>
                        )}
                      </div>

                      {/* Event pills */}
                      <div className="space-y-0.5">
                        {visibleEvents.map((ev) => (
                          <EventPill key={ev.id} event={ev} onClick={setSelectedEvent} />
                        ))}
                        {extraEvents.length > 0 && (
                          <MoreEventsButton
                            count={extraEvents.length}
                            events={extraEvents}
                            onEventClick={setSelectedEvent}
                          />
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          )}

          {/* Legend */}
          <div className="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
            <div className="flex flex-wrap gap-4 justify-center">
              {Object.entries(EVENT_CONFIG).map(([key, cfg]) => (
                <div key={key} className="flex items-center gap-1.5 text-xs text-slate-600">
                  <span className={`w-2.5 h-2.5 rounded-full ${cfg.dot}`}></span>
                  <span className="font-medium">{cfg.label}</span>
                </div>
              ))}
              <div className="flex items-center gap-1.5 text-xs text-slate-600">
                <span className="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <span className="font-medium line-through">Sudah Expired</span>
              </div>
            </div>
          </div>
        </div>

        {/* ─── Upcoming Events Panel ──────────────────────── */}
        {showUpcoming && (
          <div className="hidden lg:block w-72 flex-shrink-0">
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
              <div className="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-primary/5 to-transparent">
                <h3 className="text-sm font-bold text-slate-800 flex items-center gap-2">
                  <CalendarClock size={16} className="text-primary" />
                  Upcoming Events
                </h3>
                <p className="text-[10px] text-slate-500 mt-0.5">60 hari ke depan</p>
              </div>
              <div className="max-h-[calc(100vh-320px)] overflow-y-auto">
                {upcomingEvents.length === 0 ? (
                  <div className="p-6 text-center text-slate-400 text-xs">
                    <CalendarDays size={28} className="mx-auto mb-2 opacity-40" />
                    Tidak ada event mendatang
                  </div>
                ) : (
                  <div className="divide-y divide-slate-50">
                    {upcomingEvents.map((ev) => {
                      const cfg = EVENT_CONFIG[ev.type];
                      const diff = daysDiff(ev.date);
                      return (
                        <button
                          key={ev.id}
                          onClick={() => setSelectedEvent(ev)}
                          className="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors group"
                        >
                          <div className="flex items-start gap-2.5">
                            <div className={`w-2 h-2 rounded-full mt-1.5 flex-shrink-0 ${cfg?.dot || 'bg-slate-400'}`} />
                            <div className="min-w-0 flex-1">
                              <div className="text-xs font-semibold text-slate-700 truncate group-hover:text-primary transition-colors">
                                {ev.santri.nama}
                              </div>
                              <div className={`text-[10px] font-medium ${cfg?.text || 'text-slate-500'}`}>
                                {cfg?.tagLabel || ev.type}
                              </div>
                              <div className="text-[10px] text-slate-400 mt-0.5 flex items-center justify-between">
                                <span>{formatDateID(ev.date)}</span>
                                <span className={`font-semibold ${
                                  diff <= 7 ? 'text-red-500' : diff <= 30 ? 'text-amber-500' : 'text-emerald-500'
                                }`}>
                                  {diff === 0 ? 'Hari ini' : `${diff}h`}
                                </span>
                              </div>
                            </div>
                          </div>
                        </button>
                      );
                    })}
                  </div>
                )}
              </div>
              {/* Quick Stats */}
              <div className="border-t border-slate-100 px-4 py-3 bg-slate-50/50">
                <div className="flex items-center justify-between text-[10px] text-slate-500">
                  <span className="flex items-center gap-1">
                    <Users size={12} />
                    Total Events
                  </span>
                  <span className="font-bold text-slate-700">{stats?.totalEvents || 0}</span>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* ─── Detail Modal ─────────────────────────────────── */}
      {selectedEvent && (
        <DetailModal event={selectedEvent} onClose={() => setSelectedEvent(null)} />
      )}
    </div>
  );
};

export default KalenderExpiry;
