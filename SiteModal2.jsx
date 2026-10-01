import SiteStaffModal from './SiteStaffModal';
import SiteSubcontractorModal from './SiteSubcontractorModal';
import BOQItemModal from "./BOQItemModal";
import React, { useState, useEffect } from 'react';
import { X, Plus, Trash2 } from 'lucide-react';
import SiteInchargeModal from './SiteInchargeModal';

export default function SiteModal({ isOpen, onClose, onSuccess, initialData = null, mode = 'create' }) {
  const [loading, setLoading] = useState(false);
  const [isAddInchargeModalOpen, setIsAddInchargeModalOpen] = useState(false);
  const [isBOQModalOpen, setIsBOQModalOpen] = useState(false);
  const [editingBOQIndex, setEditingBOQIndex] = useState(null);
  const [isStaffModalOpen, setIsStaffModalOpen] = useState(false);
  const [editingStaffIndex, setEditingStaffIndex] = useState(null);
  const [isSubModalOpen, setIsSubModalOpen] = useState(false);
  const [editingSubIndex, setEditingSubIndex] = useState(null);
  const [error, setError] = useState('');
  
  const [formData, setFormData] = useState({
    name: '',
    
    site_incharge_id: '',
          boq_name: '',
          agreement_value: '',
          upto_date_bill_value: '',
          balance_work_value: '',
          site_exp: '',
          np: '',
    staff: [],
    subcontractors: [],
    boq_items: []
  });

  
  const [incharges, setIncharges] = useState([]);



  const handleSubChange = (index, field, value) => {
    const newSubs = [...formData.subcontractors];
    newSubs[index][field] = value;
    setFormData({ ...formData, subcontractors: newSubs });
  };

  const addSub = () => {
    setFormData({ ...formData, subcontractors: [...formData.subcontractors, { name: '' }] });
  };

  const removeSub = (index) => {
    const newSubs = formData.subcontractors.filter((_, i) => i !== index);
    setFormData({ ...formData, subcontractors: newSubs });
  };

  const handleStaffChange = (index, field, value) => {
    const newStaff = [...formData.staff];
    newStaff[index][field] = value;
    setFormData({ ...formData, staff: newStaff });
  };

  const addStaff = () => {
    setFormData({ ...formData, staff: [...formData.staff, { name: '', salary: '' }] });
  };

  const removeStaff = (index) => {
    const newStaff = formData.staff.filter((_, i) => i !== index);
    setFormData({ ...formData, staff: newStaff });
  };

  const isView = mode === 'view';

  useEffect(() => {
    const fetchDropdowns = async () => {
      try {
        const token = localStorage.getItem('admin_token');
        const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        
        const [inchRes] = await Promise.all([
          
          fetch(`${apiUrl}/site-incharges`, { headers: { 'Authorization': `Bearer ${token}` } })
        ]);
        
        
        if (inchRes.ok) setIncharges(await inchRes.json());
      } catch (e) {
        console.error(e);
      }
    };
    
    if (isOpen) {
      fetchDropdowns();
      
      if (initialData && (mode === 'edit' || mode === 'view')) {
        setFormData({
          name: initialData.name || '',
          
          site_incharge_id: initialData.site_incharge_id || '',
          boq_name: initialData.boq_name || '',
          agreement_value: initialData.agreement_value || '',
          upto_date_bill_value: initialData.upto_date_bill_value || '',
          balance_work_value: initialData.balance_work_value || '',
          site_exp: initialData.site_exp || '',
          np: initialData.np || '',
          staff: initialData.site_staff || [],
          subcontractors: initialData.site_subcontractors || []
        });
      } else {
        setFormData({
          name: '',
          
          site_incharge_id: '',
          boq_name: '',
          agreement_value: '',
          upto_date_bill_value: '',
          balance_work_value: '',
          site_exp: '',
          np: '',
          staff: [],
          subcontractors: [],
    boq_items: []
        });
      }
      setError('');
    }
  }, [isOpen, initialData, mode]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (isView) return;
    
    setLoading(true);
    setError('');

    try {
      const token = localStorage.getItem('admin_token');
      const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
      const url = mode === 'edit' ? `${apiUrl}/sites/${initialData.id}` : `${apiUrl}/sites`;
      
      const response = await fetch(url, {
        method: mode === 'edit' ? 'PUT' : 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      if (response.ok) {
        onSuccess();
        onClose();
      } else {
        const data = await response.json();
        setError(data.message || 'Failed to save site');
      }
    } catch (error) {
      console.error('Error saving site:', error);
      setError('An error occurred while saving.');
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="site-modal-overlay fixed inset-0 bg-gray-900/60 dark:bg-black/80 z-50 flex items-center justify-center p-4 transition-colors duration-300">
      <div className="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg flex flex-col max-h-[90vh] transition-colors duration-300">
        
        <div className="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center shrink-0">
          <h2 className="text-xl font-bold text-gray-900 dark:text-white">
            {mode === 'edit' ? 'Edit Site' : isView ? 'Site Details' : 'Add New Site'}
          </h2>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <X className="w-6 h-6" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
          {error && (
            <div className="p-3 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm">
              {error}
            </div>
          )}

          <div className="grid grid-cols-1 gap-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
<div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Name *</label>
              <input 
                type="text" 
                required
                disabled={isView}
                value={formData.name} 
                onChange={(e) => setFormData({...formData, name: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder="Enter site name"
              />
            </div>
<div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Incharge *</label>
              <div className="flex items-center gap-2">
                <select required disabled={isView} value={formData.site_incharge_id} onChange={(e) => setFormData({...formData, site_incharge_id: e.target.value})} className="flex-1 px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <option value="">Select Incharge</option>
                {incharges.map(i => <option key={i.id} value={i.id}>{i.name}</option>)}
                </select>
                <button
                  type="button"
                  onClick={() => setIsAddInchargeModalOpen(true)}
                  className="p-2.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                  title="Add New Site Incharge"
                >
                  <Plus className="w-5 h-5" />
                </button>
              </div>
            </div>
</div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BOQ Name</label>
              <input 
                type="text" 
                disabled={isView}
                value={formData.boq_name || ''} 
                onChange={(e) => setFormData({...formData, boq_name: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder="Enter BOQ name"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agreement Value</label>
              <input 
                type="number" 
                disabled={isView || (formData.boq_items && formData.boq_items.length > 0)}
                value={formData.agreement_value || ''} 
                onChange={(e) => setFormData({...formData, agreement_value: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder={formData.boq_items && formData.boq_items.length > 0 ? "Auto-calculated from BOQ" : "Enter amount"}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upto Date Bill Value</label>
              <input 
                type="number" 
                disabled={isView || (formData.boq_items && formData.boq_items.length > 0)}
                value={formData.upto_date_bill_value || ''} 
                onChange={(e) => setFormData({...formData, upto_date_bill_value: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder={formData.boq_items && formData.boq_items.length > 0 ? "Auto-calculated from BOQ" : "Enter amount"}
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Balance Work Value</label>
              <input 
                type="number" 
                disabled={isView || (formData.boq_items && formData.boq_items.length > 0)}
                value={formData.balance_work_value || ''} 
                onChange={(e) => setFormData({...formData, balance_work_value: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder={formData.boq_items && formData.boq_items.length > 0 ? "Auto-calculated from BOQ" : "Enter amount"}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Exp</label>
              <input 
                type="number" 
                disabled={isView}
                value={formData.site_exp || ''} 
                onChange={(e) => setFormData({...formData, site_exp: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder="Enter Site Exp"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NP</label>
              <input 
                type="number" 
                disabled={isView}
                value={formData.np || ''} 
                onChange={(e) => setFormData({...formData, np: e.target.value})} 
                className="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white disabled:opacity-70" 
                placeholder="Enter NP"
              />
            </div>
          </div>


            
          </div>
          {/* Staff Section */}
          <div className="pt-6 border-t border-gray-200 dark:border-gray-800">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Site Staff</h3>
              {!isView && (
                <button type="button" onClick={() => { setEditingStaffIndex(null); setIsStaffModalOpen(true); }} className="text-blue-600 hover:text-blue-700 text-xs flex items-center gap-1 font-medium bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg transition-colors">
                  + Add Staff
                </button>
              )}
            </div>
            
            <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table className="w-full text-sm text-left border-collapse">
                <thead className="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-600 dark:text-gray-400">
                  <tr>
                    <th className="px-4 py-3 font-semibold">Name</th>
                    <th className="px-4 py-3 font-semibold">Base Salary</th>
                    {!isView && <th className="px-4 py-3 w-16"></th>}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                  {(formData.staff || []).length === 0 && (
                    <tr><td colSpan="3" className="px-4 py-8 text-center text-gray-500 bg-gray-50/50 dark:bg-gray-800/30">No staff added yet.</td></tr>
                  )}
                  {(formData.staff || []).map((staffMember, index) => (
                    <tr key={index} className="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                      <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{staffMember.name}</td>
                      <td className="px-4 py-3">₹{staffMember.salary || 0}</td>
                      {!isView && (
                        <td className="px-4 py-3 text-center">
                          <div className="flex justify-center gap-2">
                            <button type="button" onClick={() => { setEditingStaffIndex(index); setIsStaffModalOpen(true); }} className="text-blue-500 hover:text-blue-700">✎</button>
                            <button type="button" onClick={() => setFormData({...formData, staff: formData.staff.filter((_, i) => i !== index)})} className="text-gray-400 hover:text-red-500">✕</button>
                          </div>
                        </td>
                      )}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          {/* Subcontractors Section */}
          <div className="pt-6 border-t border-gray-200 dark:border-gray-800">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Site Subcontractors</h3>
              {!isView && (
                <button type="button" onClick={() => { setEditingSubIndex(null); setIsSubModalOpen(true); }} className="text-blue-600 hover:text-blue-700 text-xs flex items-center gap-1 font-medium bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg transition-colors">
                  + Add Subcontractor
                </button>
              )}
            </div>
            
            <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table className="w-full text-sm text-left border-collapse">
                <thead className="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-600 dark:text-gray-400">
                  <tr>
                    <th className="px-4 py-3 font-semibold">Name</th>
                    <th className="px-4 py-3 font-semibold">No of Labour</th>
                    <th className="px-4 py-3 font-semibold">Work Details</th>
                    {!isView && <th className="px-4 py-3 w-16"></th>}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                  {(formData.subcontractors || []).length === 0 && (
                    <tr><td colSpan="4" className="px-4 py-8 text-center text-gray-500 bg-gray-50/50 dark:bg-gray-800/30">No subcontractors added yet.</td></tr>
                  )}
                  {(formData.subcontractors || []).map((sub, index) => (
                    <tr key={index} className="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                      <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{sub.name}</td>
                      <td className="px-4 py-3">{sub.no_of_labour || '-'}</td>
                      <td className="px-4 py-3">{sub.work_details || '-'}</td>
                      {!isView && (
                        <td className="px-4 py-3 text-center">
                          <div className="flex justify-center gap-2">
                            <button type="button" onClick={() => { setEditingSubIndex(index); setIsSubModalOpen(true); }} className="text-blue-500 hover:text-blue-700">✎</button>
                            <button type="button" onClick={() => setFormData({...formData, subcontractors: formData.subcontractors.filter((_, i) => i !== index)})} className="text-gray-400 hover:text-red-500">✕</button>
                          </div>
                        </td>
                      )}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {/* BOQ Items Section */}
          <div className="pt-6 border-t border-gray-200 dark:border-gray-800">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">BOQ Details</h3>
              {!isView && (
                <button type="button" onClick={() => { setEditingBOQIndex(null); setIsBOQModalOpen(true); }} className="text-blue-600 hover:text-blue-700 text-xs flex items-center gap-1 font-medium bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg transition-colors">
                  + Add BOQ Item
                </button>
              )}
            </div>
            
            <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table className="w-full text-sm text-left border-collapse">
                <thead className="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-600 dark:text-gray-400">
                  <tr>
                    <th className="px-4 py-3 font-semibold w-12 text-center">Sl no</th>
                    <th className="px-4 py-3 font-semibold">item</th>
                    <th className="px-4 py-3 font-semibold text-right">Est. qnt</th>
                    <th className="px-4 py-3 font-semibold text-center">unit</th>
                    <th className="px-4 py-3 font-semibold text-right">rate</th>
                    <th className="px-4 py-3 font-semibold text-right">Amount</th>
                    <th className="px-4 py-3 font-semibold text-right">work done qty</th>
                    <th className="px-4 py-3 font-semibold text-right">Amount</th>
                    <th className="px-4 py-3 font-semibold text-right">Balance Qty</th>
                    {!isView && <th className="px-4 py-3 w-16"></th>}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                  {(formData.boq_items || []).length === 0 && (
                    <tr><td colSpan="10" className="px-4 py-8 text-center text-gray-500 bg-gray-50/50 dark:bg-gray-800/30 border-2 border-dashed border-gray-200 dark:border-gray-700 m-2 rounded-xl">No BOQ items added yet. Click "Add BOQ Item" to add details.</td></tr>
                  )}
                  {(formData.boq_items || []).map((item, index) => {
                    const est = parseFloat(item.est_qnt) || 0;
                    const rate = parseFloat(item.rate) || 0;
                    const wd = parseFloat(item.work_done_qty) || 0;
                    return (
                      <tr key={index} className="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                        <td className="px-4 py-3 text-center font-medium text-gray-500">{index + 1}</td>
                        <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{item.item_name}</td>
                        <td className="px-4 py-3 text-right">{est}</td>
                        <td className="px-4 py-3 text-center text-gray-500">{item.unit}</td>
                        <td className="px-4 py-3 text-right">₹{rate}</td>
                        <td className="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">₹{(est * rate).toFixed(2)}</td>
                        <td className="px-4 py-3 text-right">{wd}</td>
                        <td className="px-4 py-3 text-right font-medium text-blue-600 dark:text-blue-400">₹{(wd * rate).toFixed(2)}</td>
                        <td className="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">{(est - wd).toFixed(2)}</td>
                        {!isView && (
                          <td className="px-4 py-3 text-center">
                            <div className="flex justify-center gap-2">
                              <button type="button" onClick={() => { setEditingBOQIndex(index); setIsBOQModalOpen(true); }} className="text-blue-500 hover:text-blue-700">✎</button>
                              <button type="button" onClick={() => setFormData({...formData, boq_items: formData.boq_items.filter((_, i) => i !== index)})} className="text-gray-400 hover:text-red-500">✕</button>
                            </div>
                          </td>
                        )}
                      </tr>
                    );
                  })}
                  {(formData.boq_items || []).length > 0 && (
                    <tr className="font-bold bg-gray-50/80 dark:bg-gray-800/80 border-t-2 border-gray-200 dark:border-gray-700">
                      <td colSpan="5" className="px-4 py-3 text-right text-gray-700 dark:text-gray-300">TOTAL</td>
                      <td className="px-4 py-3 text-right text-gray-900 dark:text-gray-100">₹{(formData.boq_items || []).reduce((sum, item) => sum + (parseFloat(item.est_qnt)||0)*(parseFloat(item.rate)||0), 0).toFixed(2)}</td>
                      <td className="px-4 py-3"></td>
                      <td className="px-4 py-3 text-right text-blue-600 dark:text-blue-400">₹{(formData.boq_items || []).reduce((sum, item) => sum + (parseFloat(item.work_done_qty)||0)*(parseFloat(item.rate)||0), 0).toFixed(2)}</td>
                      <td colSpan={isView ? 1 : 2}></td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>



          <div className="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-800">
            <button 
              type="button" 
              onClick={onClose}
              className="px-6 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium"
            >
              {isView ? 'Close' : 'Cancel'}
            </button>
            {!isView && (
              <button 
                type="submit"
                disabled={loading}
                className="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 dark:hover:bg-blue-500 transition-colors font-medium disabled:opacity-50"
              >
                {loading ? 'Saving...' : 'Save Site'}
              </button>
            )}
          </div>
        </form>
      </div>

      {isAddInchargeModalOpen && (
        <SiteInchargeModal
          isOpen={isAddInchargeModalOpen}
          
          onClose={() => setIsAddInchargeModalOpen(false)}
          onSuccess={() => {
            // Need to refetch incharges to show the new one
            const fetchIncharges = async () => {
              const token = localStorage.getItem('admin_token');
              const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
              try {
                const res = await fetch(`${apiUrl}/site-incharges`, { headers: { 'Authorization': `Bearer ${token}` } });
                if (res.ok) {
                  const data = await res.json();
                  setIncharges(data);
                }
              } catch(e) {}
            };
            fetchIncharges();
            setIsAddInchargeModalOpen(false);
          }}
          mode="create"
        />
      )}

      
      <SiteStaffModal 
        isOpen={isStaffModalOpen} 
        onClose={() => setIsStaffModalOpen(false)}
        initialData={editingStaffIndex !== null ? formData.staff[editingStaffIndex] : null}
        onSave={(itemData) => {
          const newItems = [...(formData.staff || [])];
          if (editingStaffIndex !== null) newItems[editingStaffIndex] = itemData;
          else newItems.push(itemData);
          setFormData({...formData, staff: newItems});
        }}
      />
      <SiteSubcontractorModal 
        isOpen={isSubModalOpen} 
        onClose={() => setIsSubModalOpen(false)}
        initialData={editingSubIndex !== null ? formData.subcontractors[editingSubIndex] : null}
        onSave={(itemData) => {
          const newItems = [...(formData.subcontractors || [])];
          if (editingSubIndex !== null) newItems[editingSubIndex] = itemData;
          else newItems.push(itemData);
          setFormData({...formData, subcontractors: newItems});
        }}
      />

      <BOQItemModal 
        isOpen={isBOQModalOpen} 
        onClose={() => setIsBOQModalOpen(false)}
        initialData={editingBOQIndex !== null ? formData.boq_items[editingBOQIndex] : null}
        onSave={(itemData) => {
          const newItems = [...(formData.boq_items || [])];
          if (editingBOQIndex !== null) {
            newItems[editingBOQIndex] = itemData;
          } else {
            newItems.push(itemData);
          }
          setFormData({...formData, boq_items: newItems});
        }}
      />
    </div>
  );
}

