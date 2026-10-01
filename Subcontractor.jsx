import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Eye } from 'lucide-react';
import SubcontractorModal from '../components/SubcontractorModal';

export default function Subcontractor() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState('create');
  const [selectedRecord, setSelectedRecord] = useState(null);
  const [records, setRecords] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchRecords = async () => {
    try {
      const token = localStorage.getItem('admin_token');
      const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      
      const response = await fetch(`${apiUrl}/subcontractors`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        }
      });
      
      if (response.ok) {
        const data = await response.json();
        setRecords(data);
      }
    } catch (error) {
      console.error('Failed to fetch subcontractor records:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchRecords();
  }, []);

  const handleSuccess = () => {
    fetchRecords();
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
    if (!window.confirm('Are you sure you want to delete this record?')) return;
    
    try {
      const token = localStorage.getItem('admin_token');
      const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      
      const response = await fetch(`${apiUrl}/subcontractors/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        }
      });
      
      if (response.ok) {
        fetchRecords();
      } else {
        alert('Failed to delete record');
      }
    } catch (error) {
      console.error('Error deleting record:', error);
      alert('Error deleting record');
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 className="text-lg md:text-xl font-bold text-gray-900 dark:text-white">SUBCONTRACTOR</h2>
          <p className="text-gray-500 dark:text-gray-400 text-xs md:text-sm">Manage subcontract labour and work details</p>
        </div>
        <button 
          onClick={() => handleOpenModal('create')}
          className="w-full sm:w-auto flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition-all shadow-lg shadow-blue-600/20"
        >
          <Plus className="w-5 h-5" />
          Add Record
        </button>
      </div>

      <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-xl transition-colors duration-300">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm whitespace-nowrap">
            <thead className="bg-gray-50 dark:bg-gray-950/50 border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
              <tr>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Date</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Site</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Source</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Subcontract Labour</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">Amount</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 w-1/3">Work Details</th>
                <th className="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 text-center">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200 dark:divide-gray-800 transition-colors duration-300">
              {loading ? (
                <tr>
                  <td colSpan="7" className="px-6 py-8 text-center text-gray-500">Loading data...</td>
                </tr>
              ) : records.length === 0 ? (
                <tr>
                  <td colSpan="7" className="px-6 py-8 text-center text-gray-500">No records found.</td>
                </tr>
              ) : (
                records.map((record) => (
                  <tr key={record.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                    <td className="px-6 py-4 text-gray-900 dark:text-white">{record.date || '-'}</td>
                    <td className="px-6 py-4 text-gray-900 dark:text-white max-w-[150px] truncate">{record.site?.name || '-'}</td>
                    <td className="px-6 py-4"><span className={`px-2 py-1 text-xs rounded-md ${record.source === 'Daily Report' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}`}>{record.source || 'Standalone'}</span></td>
                    <td className="px-6 py-4 font-medium text-gray-900 dark:text-white">{record.name}</td>
                    <td className="px-6 py-4 text-gray-700 dark:text-gray-300">{record.amount}</td>
                    <td className="px-6 py-4 text-gray-700 dark:text-gray-300 truncate max-w-[200px]" title={record.work_details}>{record.work_details || '-'}</td>
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center gap-3">
                        {record.source === 'Daily Report' ? (
                          <span className="text-xs text-gray-400 italic">Edit in Daily Report</span>
                        ) : (
                          <>
                            <button onClick={() => handleOpenModal('view', record)} className="text-gray-400 hover:text-blue-600 transition-colors" title="View">
                              <Eye className="w-4 h-4" />
                            </button>
                            <button onClick={() => handleOpenModal('edit', record)} className="text-gray-400 hover:text-amber-600 transition-colors" title="Edit">
                              <Edit2 className="w-4 h-4" />
                            </button>
                            <button onClick={() => handleDelete(record.id)} className="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      <SubcontractorModal 
        isOpen={isModalOpen} 
        onClose={handleCloseModal} 
        onSuccess={handleSuccess}
        initialData={selectedRecord}
        mode={modalMode}
      />
    </div>
  );
}
