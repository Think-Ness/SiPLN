import { Outlet, NavLink } from 'react-router-dom';
import { LayoutDashboard, Users, FileCheck, Archive, Menu, Bell, Folder, CalendarDays } from 'lucide-react';
import { useState } from 'react';

const Layout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const menuItems = [
    { title: 'Dashboard', icon: <LayoutDashboard size={20} />, path: '/dashboard' },
    { title: 'Data Santri', icon: <Users size={20} />, path: '/master-data' },
    { title: 'Auto Rekap', icon: <FileCheck size={20} />, path: '/auto-rekap' },
    { title: 'Kalender Expiry', icon: <CalendarDays size={20} />, path: '/kalender-expiry' },
    { title: 'Inaktif Data', icon: <Archive size={20} />, path: '/inaktif' },
    { title: 'Pemberkasan', icon: <Folder size={20} />, path: '/pemberkasan' },
  ];

  return (
    <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
      {/* Sidebar */}
      <aside className={`${sidebarOpen ? 'w-64' : 'w-20'} bg-white border-r border-slate-200 transition-all duration-300 flex flex-col`}>
        <div className="h-16 flex items-center justify-center border-b border-slate-200 bg-primary text-white font-bold text-lg">
          {sidebarOpen ? 'Sistem Informasi' : 'SI'}
        </div>
        
        <nav className="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
          {menuItems.map((item, idx) => (
            <NavLink
              key={idx}
              to={item.path}
              className={({ isActive }) => 
                `flex items-center px-3 py-2.5 rounded-lg transition-colors ${
                  isActive 
                    ? 'bg-primary/10 text-primary font-semibold' 
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                } ${!sidebarOpen && 'justify-center'}`
              }
            >
              <span className={`${sidebarOpen ? 'mr-3' : ''}`}>{item.icon}</span>
              {sidebarOpen && <span>{item.title}</span>}
            </NavLink>
          ))}
        </nav>

        <div className="p-4 border-t border-slate-200 text-xs text-slate-400 text-center">
          {sidebarOpen && 'React UI (Beta)'}
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Topbar */}
        <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-10">
          <button 
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="p-2 rounded-md text-slate-500 hover:bg-slate-100 transition-colors"
          >
            <Menu size={24} />
          </button>
          
          <div className="flex items-center gap-4">
            <button className="p-2 text-slate-400 hover:text-primary transition-colors relative">
              <Bell size={20} />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-danger rounded-full"></span>
            </button>
            <div className="flex items-center gap-2 cursor-pointer">
              <div className="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold">
                A
              </div>
              <span className="text-sm font-medium text-slate-700 hidden sm:block">Admin LN</span>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto p-6 bg-slate-50/50">
          <div className="mx-auto max-w-7xl">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};

export default Layout;
