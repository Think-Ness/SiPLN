import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import MasterData from './pages/MasterData';
import AutoRekap from './pages/AutoRekap';
import KalenderExpiry from './pages/KalenderExpiry';
import InaktifData from './pages/InaktifData';
import Pemberkasan from './pages/Pemberkasan';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="master-data" element={<MasterData />} />
          <Route path="auto-rekap" element={<AutoRekap />} />
          <Route path="kalender-expiry" element={<KalenderExpiry />} />
          <Route path="inaktif" element={<InaktifData />} />
          <Route path="pemberkasan" element={<Pemberkasan />} />
          {/* We will add more routes here in later phases */}
          <Route path="*" element={<div className="p-8 text-center text-gray-500">Halaman belum tersedia di versi React</div>} />
        </Route>
      </Routes>
    </Router>
  );
}

export default App;
