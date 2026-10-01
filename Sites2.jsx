import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit2, Trash2 } from 'lucide-react';
import SiteModal from '../components/SiteModal';

export default function Sites() {
  const navigate = useNavigate();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState('create');
  const [selectedRecord, setSelectedRecord] = useState(null);
  const [sites, setSites] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchSites = async () => {
    try {
      const token = localStorage.getItem('admin_token');
      const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      
      const response = await fetch(`${apiUrl}/sites`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        }
      });
      
      if (response.ok) {
        const data = await response.json();
        setSites(data);
      }
    } catch (error) {
      console.error('Failed to fetch sites:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSites();
  }, []);

  const handleSuccess = () => {
    fetchSites();
  };

  const handleOpenModal = (mode, record = null) => {
    setModalMode(mode);
    setSelectedRecord(record);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setSelectedRecord(null);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this site?')) {
      return;
    }
    
    try {
      const token = localStorage.getItem('admin_token');
      const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      
      const response = await fetch(`${apiUrl}/sites/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        }
      });
      
      if (response.ok) {
        fetchSites();
      } else {
        alert('Failed to delete site');
      }
    } catch (error) {
      console.error('Error deleting site:', error);
      alert('Error deleting site');
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-lg md:text-xl font-bold text-gray-900 dark:text-white">SITES</h2>
          <p className="text-gray-500 dark:text-gray-400 text-xs md:text-sm">Manage sites and site incharges</p>
        </div>
        <button 
          onClick={() => navigate('/add-site')}
          className="w-full sm:w-auto flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition-all shadow-lg shadow-blue-600/20"
        >
          <Plus className="w-5 h-5" />
          Add Site
        </button>
      </div>

      <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-xl transition-colors duration-300">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm whitespace-nowrap">
            <thead className="bg-gray-50 dark:bg-gray-950/50 border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
              <tr>
                
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Site Name</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Site Incharge</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">BOQ</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 text-right">Agreement Value</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 text-right">Bill Value</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 text-right">Balance</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 text-center">Actions</th>
              </tr>
</thead>
            <tbody className="divide-y divide-gray-200 dark:divide-gray-800 transition-colors duration-300">
              {loading ? (
                <tr>
                  <td colSpan="8" className="px-6 py-8 text-center text-gray-500">Loading data...</td>
                </tr>
              ) : sites.length === 0 ? (
                <tr>
                  <td colSpan="8" className="px-6 py-8 text-center text-gray-500">No records found. Click "Add Site" to create one.</td>
                </tr>
              ) : (
                sites.map((record) => (
                  <tr key={record.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                    
                    <td className="px-6 py-4 font-medium text-gray-900 dark:text-white">{record.name}</td>
                    <td className="px-6 py-4 text-gray-600 dark:text-gray-400">{record.site_incharge ? record.site_incharge.name : '-'}</td>
                    <td className="px-6 py-4 text-gray-600 dark:text-gray-400">{record.boq_name || '-'}</td>
                    <td className="px-6 py-4 text-right font-mono text-green-600">₹{parseFloat(record.agreement_value || 0).toLocaleString()}</td>
                    <td className="px-6 py-4 text-right font-mono text-blue-600">₹{parseFloat(record.upto_date_bill_value || 0).toLocaleString()}</td>
                    <td className="px-6 py-4 text-right font-mono text-amber-600">₹{parseFloat(record.balance_work_value || 0).toLocaleString()}</td>
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center gap-3">
                        <button onClick={() => handleOpenModal('edit', record)} className="text-gray-400 hover:text-amber-600 dark:hover:text-yellow-400 transition-colors" title="Edit">
                          <Edit2 className="w-4 h-4" />
                        </button>
                        <button onClick={() => handleDelete(record.id)} className="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
</tbody>
          </table>
        </div>
      </div>

      <SiteModal 
        isOpen={isModalOpen} 
        onClose={handleCloseModal} 
        onSuccess={handleSuccess}
        initialData={selectedRecord}
        mode={modalMode}
      />
    </div>
  );
}
