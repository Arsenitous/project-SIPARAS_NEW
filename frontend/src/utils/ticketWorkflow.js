/**
 * Utility untuk validasi aturan alur kerja (workflow) tiket SIPARAS.
 */

/**
 * Menentukan apakah user saat ini dapat menghapus tiket.
 * Sesuai aturan alur SIPARAS:
 * 1. PO / BAST / Status Selesai:
 *    Jika tiket sudah memiliki Purchase Order (PO), Berita Acara (BAST),
 *    atau status tiket sudah completed/signed_archived, tiket TIDAK DAPAT dihapus oleh siapa pun.
 * 2. Kepsek (kepsek):
 *    - Hanya tiket miliknya / unit sekolahnya.
 *    - Jalur 1 (sekolah): bisa hapus SELAMA ASP belum reviu (asp_approval_status === 'pending').
 *      Jika ASP sudah selesai reviu (approved/rejected/revision_required), tak boleh hapus.
 *    - Jalur 2 (kantor pusat / bypass ASP ke KD): bisa hapus SELAMA KD Sarpras belum reviu (kd_approval_status === 'pending').
 * 3. KD Departemen (dept_ygpkb):
 *    - Hanya tiket miliknya / unit departemennya.
 *    - Bisa hapus SELAMA KD Sarpras belum reviu (kd_approval_status === 'pending').
 * 4. ASP Wilayah (asp_wilayah):
 *    - Hanya tiket temuan lapangan buatan ASP (Jalur 3).
 *    - Bisa hapus SELAMA KD Sarpras belum reviu (kd_approval_status === 'pending').
 * 5. KD Sarpras (kd_sarpras):
 *    - Bisa hapus selama belum ada PO / BAST / pengerjaan selesai.
 * 6. Superadmin / Admin Sarpras:
 *    - Bisa hapus selama belum ada PO / BAST / pengerjaan selesai.
 *
 * @param {Object} ticket
 * @param {Object} user
 * @returns {boolean}
 */
export function canDeleteTicket(ticket, user) {
  if (!ticket || !user) return false;

  // Jika backend sudah mengirim flag eksplisit
  if (typeof ticket.can_delete === 'boolean') {
    return ticket.can_delete;
  }

  // Cek PO / BAST
  const hasPo = (ticket.purchase_orders_count && Number(ticket.purchase_orders_count) > 0) ||
                (Array.isArray(ticket.purchaseOrders) && ticket.purchaseOrders.length > 0) ||
                (Array.isArray(ticket.purchase_orders) && ticket.purchase_orders.length > 0);
  if (hasPo) return false;

  const hasBast = (ticket.bast_document_count && Number(ticket.bast_document_count) > 0) ||
                  !!ticket.bastDocument || !!ticket.bast_document;
  if (hasBast) return false;

  // Status selesai
  if (['completed', 'signed_archived'].includes(ticket.status)) {
    return false;
  }

  const role = user.role;

  if (['superadmin', 'admin_sarpras'].includes(role)) {
    return true;
  }

  if (role === 'kepsek') {
    const isOwner = Number(ticket.unit_id) === Number(user.unit_id) || Number(ticket.user_id) === Number(user.id);
    if (!isOwner) return false;

    // Kepsek HANYA bisa hapus jika tiket MASIH di tahap awal ASP belum direviu
    // Selama tiket sudah sampai ke KD Sarpras (submitted_to_kd) atau ASP sudah selesai reviu, Kepsek TIDAK BISA HAPUS!
    return ticket.status === 'submitted_to_asp' && ticket.asp_approval_status === 'pending';
  }

  if (role === 'dept_ygpkb') {
    const isOwner = Number(ticket.unit_id) === Number(user.unit_id) || Number(ticket.user_id) === Number(user.id);
    if (!isOwner) return false;

    // Kepala Departemen bisa hapus selama masih di KD Sarpras dan KD Sarpras belum reviu
    return ['submitted_to_kd', 'submitted_to_asp'].includes(ticket.status) && ticket.kd_approval_status === 'pending';
  }

  if (role === 'asp_wilayah') {
    const isCreator = Number(ticket.user_id) === Number(user.id) || ticket.routing_path === 'jalur_3_temuan_asp';
    if (!isCreator) return false;

    // ASP Wilayah bisa hapus tiket temuan lapangan selama masih di meja KD Sarpras dan KD Sarpras belum reviu
    return ticket.status === 'submitted_to_kd' && ticket.kd_approval_status === 'pending';
  }

  if (role === 'kd_sarpras') {
    return true;
  }

  return false;
}
