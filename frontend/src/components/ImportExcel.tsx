// components/ImportExcel.tsx
'use client';
import { useState } from 'react';

export default function ImportExcel() {
    const [file, setFile] = useState<File | null>(null);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setFile(e.target.files[0]);
        }
    };

    const handleUpload = async () => {
        if (!file) {
            setMessage('Vui lòng chọn file Excel hoặc CSV!');
            return;
        }

        setLoading(true);
        const formData = new FormData();
        formData.append('file', file);

        try {
            // Nhớ thay bằng domain thật của backend Laravel
            const res = await fetch('http://localhost:8000/api/import-chuyen-doi', {
                method: 'POST',
                body: formData,
            });

            const data = await res.json();
            setMessage(data.message);
        } catch (error) {
            console.error(error);
            setMessage('Có lỗi xảy ra khi upload.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="p-6 bg-white rounded-lg shadow-md max-w-md mx-auto mt-10">
            <h2 className="text-xl font-bold mb-4 text-gray-800">Nhập Danh Sách Chuyển Đổi</h2>
            <input
                type="file"
                accept=".xlsx, .xls, .csv"
                onChange={handleFileChange}
                className="mb-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            <button
                onClick={handleUpload}
                disabled={loading}
                className="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
            >
                {loading ? 'Đang xử lý...' : 'Upload Data'}
            </button>

            {message && (
                <p className="mt-4 text-sm font-medium text-center text-green-600">
                    {message}
                </p>
            )}
        </div>
    );
}