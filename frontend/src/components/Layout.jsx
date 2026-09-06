import { Outlet, NavLink, useLocation } from 'react-router-dom';
import { LayoutDashboard, Users, FileCheck, Archive, Menu, Bell, Folder, CalendarDays, X } from 'lucide-react';
import { useState, useEffect } from 'react';

const Layout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [desktopCollapsed, setDesktopCollapsed] = useState(false);
  const location = useLocation();

  // Auto-close sidebar on mobile when navigating
  useEffect(() => {
    setSidebarOpen(false);
  }, [location.pathname]);

  // Close sidebar on resize to desktop
  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth >= 1024) {
        setSidebarOpen(false);
      }
    };
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const menuItems = [
    { title: 'Dashboard', icon: <LayoutDashboard size={20} />, path: '/dashboard' },
    { title: 'Data Santri', icon: <Users size={20} />, path: '/master-data' },
    { title: 'Auto Rekap', icon: <FileCheck size={20} />, path: '/auto-rekap' },
    { title: 'Kalender Expiry', icon: <CalendarDays size={20} />, path: '/kalender-expiry' },
    { title: 'Inaktif Data', icon: <Archive size={20} />, path: '/inaktif' },
    { title: 'Pemberkasan', icon: <Folder size={20} />, path: '/pemberkasan' },
  ];

  const sidebarContent = (
    <>
      <div className="h-14 lg:h-16 flex items-center justify-between px-4 border-b border-slate-200 bg-primary text-white font-bold text-lg shrink-0">
        <span>{(!desktopCollapsed || sidebarOpen) ? 'Sistem Informasi' : 'SI'}</span>
        {/* Close button only on mobile overlay */}
        <button
          onClick={() => setSidebarOpen(false)}
          className="lg:hidden p-1 rounded-md hover:bg-white/20 transition-colors"
        >
          <X size={20} />
        </button>
      </div>
      
      <nav className="flex-1 py-4 lg:py-6 px-3 space-y-1 overflow-y-auto">
        {menuItems.map((item, idx) => (
          <NavLink
            key={idx}
            to={item.path}
            className={({ isActive }) => 
              `flex items-center px-3 py-2.5 rounded-lg transition-colors ${
                isActive 
                  ? 'bg-primary/10 text-primary font-semibold' 
                  : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
              } ${!sidebarOpen && desktopCollapsed ? 'lg:justify-center' : ''}`
            }
          >
            <span className={`${(sidebarOpen || !desktopCollapsed) ? 'mr-3' : 'lg:mr-0 mr-3'}`}>{item.icon}</span>
            {(sidebarOpen || !desktopCollapsed) && <span>{item.title}</span>}
            {!sidebarOpen && desktopCollapsed && <span className="lg:hidden">{item.title}</span>}
          </NavLink>
        ))}
      </nav>

      <div className="p-4 border-t border-slate-200 text-xs text-slate-400 text-center shrink-0">
        {(sidebarOpen || !desktopCollapsed) && 'React UI (Beta)'}
      </div>
    </>
  );

  return (
    <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
      {/* Mobile Overlay Backdrop */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar - Desktop: static, Mobile: overlay drawer */}
      <aside className={`
        fixed lg:relative inset-y-0 left-0 z-50 lg:z-auto
        bg-white border-r border-slate-200 flex flex-col
        transition-all duration-300 ease-in-out
        ${sidebarOpen 
          ? 'w-64 translate-x-0' 
          : '-translate-x-full lg:translate-x-0'
        }
        ${desktopCollapsed && !sidebarOpen ? 'lg:w-20' : 'lg:w-64'}
      `}>
        {sidebarContent}
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden min-w-0">
        {/* Topbar */}
        <header className="h-14 lg:h-16 bg-white border-b border-slate-200 flex items-center justify-between px-3 sm:px-4 lg:px-6 shadow-sm z-10 shrink-0">
          <div className="flex items-center gap-2">
            {/* Mobile: always show hamburger; Desktop: toggle collapse */}
            <button 
              onClick={() => {
                if (window.innerWidth < 1024) {
                  setSidebarOpen(!sidebarOpen);
                } else {
                  setDesktopCollapsed(!desktopCollapsed);
                }
              }}
              className="p-2 rounded-md text-slate-500 hover:bg-slate-100 transition-colors"
            >
              <Menu size={22} />
            </button>
          </div>
          
          <div className="flex items-center gap-2 sm:gap-4">
            <button className="p-2 text-slate-400 hover:text-primary transition-colors relative">
              <Bell size={20} />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-danger rounded-full"></span>
            </button>
            <div className="flex items-center gap-2 cursor-pointer">
              <div className="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm">
                A
              </div>
              <span className="text-sm font-medium text-slate-700 hidden sm:block">Admin LN</span>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto p-3 sm:p-4 lg:p-6 bg-slate-50/50">
          <div className="mx-auto max-w-7xl">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};

export default Layout;
