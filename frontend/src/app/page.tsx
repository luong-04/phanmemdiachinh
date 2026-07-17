'use client';
import { useState, useRef, useEffect } from 'react';

export default function Home() {
  const [activeTab, setActiveTab] = useState<'khong-gian' | 'phap-ly'>('khong-gian');
  const [file, setFile] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [dsChuyenDoi, setDsChuyenDoi] = useState<any[]>([]); // State lưu dữ liệu bảng

  const fileInputRef = useRef<HTMLInputElement>(null);

  // Hàm gọi API lấy dữ liệu từ Backend
  const fetchData = async () => {
    try {
      const res = await fetch('http://localhost:8000/api/get-chuyen-doi');
      const result = await res.json();
      if (result.status === 'success') {
        setDsChuyenDoi(result.data);
      }
    } catch (error) {
      console.error('Lỗi khi tải dữ liệu:', error);
    }
  };

  // Tự động lấy dữ liệu khi chuyển sang tab pháp lý
  useEffect(() => {
    if (activeTab === 'phap-ly') {
      fetchData();
    }
  }, [activeTab]);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files.length > 0) {
      setFile(e.target.files[0]);
    }
  };

  const triggerFileInput = () => {
    if (fileInputRef.current) {
      fileInputRef.current.click();
    }
  };

  const handleTabChange = (tab: 'khong-gian' | 'phap-ly') => {
    setActiveTab(tab);
    setFile(null);
  };

  const handleUpload = async () => {
    if (!file) {
      alert('Vui lòng chọn file trước khi tải lên!');
      return;
    }

    setLoading(true);

    if (activeTab === 'phap-ly') {
      const formData = new FormData();
      formData.append('file', file);

      try {
        const res = await fetch('http://localhost:8000/api/import-chuyen-doi', {
          method: 'POST',
          body: formData,
        });
        const data = await res.json();
        alert(data.message || 'Xử lý thành công!');
        fetchData(); // Tải lại bảng ngay sau khi upload thành công
        setFile(null); // Reset file
      } catch (error) {
        console.error(error);
        alert('Có lỗi kết nối đến Server Laravel!');
      }
    } else {
      alert(`Đã nhận file không gian: ${file.name}. Sẵn sàng bóc tách tọa độ!`);
    }

    setLoading(false);
  };

  return (
    <main className="p-6 max-w-7xl mx-auto">
      <input
        type="file"
        ref={fileInputRef}
        className="hidden"
        accept={activeTab === 'khong-gian' ? '.txt,.pol,.gtp,.dgn' : '.xlsx,.xls,.csv'}
        onChange={handleFileChange}
      />

      <div className="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
        <h1 className="text-2xl font-bold text-blue-700">HỆ THỐNG PHẦN MỀM ĐỊA CHÍNH</h1>
        <p className="text-gray-500 text-sm mt-1">Phân hệ xử lý dữ liệu đo đạc & Hồ sơ pháp lý xã Vĩnh An</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* CỘT TRÁI */}
        <div className="lg:col-span-4 flex flex-col gap-4">
          <div className="flex bg-white rounded-lg shadow-sm border border-gray-100 p-1">
            <button
              onClick={() => handleTabChange('khong-gian')}
              className={`flex-1 py-2 text-sm font-medium rounded-md transition-colors ${activeTab === 'khong-gian' ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50'
                }`}
            >
              Dữ liệu Tọa độ
            </button>
            <button
              onClick={() => handleTabChange('phap-ly')}
              className={`flex-1 py-2 text-sm font-medium rounded-md transition-colors ${activeTab === 'phap-ly' ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50'
                }`}
            >
              Hồ sơ Pháp lý
            </button>
          </div>

          <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex-1 flex flex-col">
            <h2 className="font-semibold text-gray-800 mb-4">
              {activeTab === 'khong-gian' ? 'Trút file máy toàn đạc / Bản đồ' : 'Nhập Sổ Mục Kê / Danh sách'}
            </h2>

            <div
              onClick={triggerFileInput}
              className={`flex-1 border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center min-h-[200px] ${file ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:bg-gray-50'
                }`}
            >
              <div className="text-4xl mb-3">{file ? '✅' : '📁'}</div>
              {file ? (
                <div>
                  <p className="text-sm font-bold text-blue-700 break-all">{file.name}</p>
                  <p className="text-xs text-gray-500 mt-1">{(file.size / 1024).toFixed(1)} KB</p>
                  <p className="text-xs text-blue-500 mt-2 underline">Nhấn để đổi file khác</p>
                </div>
              ) : (
                <div>
                  <p className="text-sm font-medium text-gray-600">
                    {activeTab === 'khong-gian' ? 'Chọn file .TXT, .POL, .GTP, .DGN' : 'Chọn file .XLSX, .XLS, .CSV'}
                  </p>
                  <p className="text-xs text-gray-400 mt-2">Dung lượng tối đa 10MB</p>
                </div>
              )}
            </div>

            <button
              onClick={handleUpload}
              disabled={loading || !file}
              className={`w-full mt-4 font-medium py-2.5 rounded-lg transition-colors text-white ${loading || !file ? 'bg-blue-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'
                }`}
            >
              {loading ? 'Đang xử lý...' : (activeTab === 'khong-gian' ? 'Bắt đầu trút tọa độ' : 'Tải lên CSDL')}
            </button>
          </div>
        </div>

        {/* CỘT PHẢI */}
        <div className="lg:col-span-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col min-h-[500px]">
          <div className="flex justify-between items-center mb-4 border-b pb-3">
            <h2 className="font-semibold text-gray-800">
              {activeTab === 'khong-gian' ? 'Bản đồ trực quan' : 'Dữ liệu CSDL Hành Chính'}
            </h2>
            {activeTab === 'khong-gian' && (
              <div className="flex gap-2">
                <button className="px-3 py-1 text-xs border rounded hover:bg-gray-50">Xem danh sách điểm</button>
                <button className="px-3 py-1 text-xs border rounded hover:bg-gray-50">Xuất file</button>
              </div>
            )}
            {activeTab === 'phap-ly' && (
              <button onClick={fetchData} className="px-3 py-1 text-xs bg-gray-100 rounded hover:bg-gray-200">🔄 Làm mới</button>
            )}
          </div>

          <div className={`flex-1 rounded-lg flex ${dsChuyenDoi.length === 0 && activeTab === 'phap-ly' ? 'items-center justify-center bg-gray-50 border border-gray-200 text-gray-400' : ''}`}>
            {activeTab === 'khong-gian' ? (
              <div className="w-full h-full flex items-center justify-center bg-gray-50 border border-gray-200 text-gray-400 rounded-lg">
                Khu vực này sẽ hiển thị Bản đồ WebGIS (Leaflet) sau khi trút tọa độ
              </div>
            ) : (
              dsChuyenDoi.length > 0 ? (
                <div className="overflow-x-auto w-full h-[500px] overflow-y-auto border border-gray-200 rounded-lg">
                  <table className="min-w-full text-sm text-left">
                    <thead className="bg-gray-100 text-gray-700 sticky top-0">
                      <tr>
                        <th className="px-4 py-3 font-semibold border-b">Chi nhánh Cũ</th>
                        <th className="px-4 py-3 font-semibold border-b">Tờ Cũ</th>
                        <th className="px-4 py-3 font-semibold border-b">Chi nhánh Mới</th>
                        <th className="px-4 py-3 font-semibold border-b">Xã Mới</th>
                        <th className="px-4 py-3 font-semibold border-b">Tờ Mới</th>
                      </tr>
                    </thead>
                    <tbody>
                      {dsChuyenDoi.map((row, index) => (
                        <tr key={index} className="border-b hover:bg-gray-50">
                          <td className="px-4 py-2">{row.chi_nhanh_cu}</td>
                          <td className="px-4 py-2 font-medium text-blue-600">{row.to_bddc_cu}</td>
                          <td className="px-4 py-2">{row.chi_nhanh_moi}</td>
                          <td className="px-4 py-2">{row.xa_moi}</td>
                          <td className="px-4 py-2 font-medium text-green-600">{row.to_bddc_moi}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <span>Chưa có dữ liệu. Vui lòng tải lên file pháp lý.</span>
              )
            )}
          </div>
        </div>
      </div>
    </main>
  );
}