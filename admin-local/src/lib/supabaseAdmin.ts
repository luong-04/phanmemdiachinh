import { createClient } from '@supabase/supabase-js';

// Khởi tạo Supabase Client với quyền Super Admin
const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY!;

export const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey, {
    auth: {
        autoRefreshToken: false,
        persistSession: false,
    },
});

// Hàm cấp phát tài khoản mới cho người dùng
export async function createTenantAccount(email: string, password: string, fullName: string, monthsToExpire: number) {
    // 1. Tạo tài khoản trong hệ thống Auth của Supabase
    const { data: authData, error: authError } = await supabaseAdmin.auth.admin.createUser({
        email: email,
        password: password,
        email_confirm: true, // Tự động xác nhận email để dùng được luôn
    });

    if (authError) throw new Error(authError.message);

    const userId = authData.user.id;

    // 2. Tính toán ngày hết hạn
    const expiresAt = new Date();
    expiresAt.setMonth(expiresAt.getMonth() + monthsToExpire);

    // 3. Chèn thông tin vào bảng profiles để kích hoạt quyền (Bỏ qua RLS)
    const { error: profileError } = await supabaseAdmin.from('profiles').insert([
        {
            id: userId,
            full_name: fullName,
            role: 'user',
            status: 'active',
            expires_at: expiresAt.toISOString(),
        }
    ]);

    if (profileError) {
        // Nếu lỗi khi lưu profile, xóa luôn tài khoản auth để tránh rác hệ thống
        await supabaseAdmin.auth.admin.deleteUser(userId);
        throw new Error(profileError.message);
    }

    return { success: true, message: 'Cấp tài khoản thành công!', userId };
}