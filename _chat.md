✅ DONE: Icon của categories thì cần là dạng upload ảnh chứ ko phải lưu svg vào db

Đã refactor hoàn toàn hệ thống icon từ SVG strings sang file upload:
- Migration: Đổi icon_svg (text) → icon_path (string, nullable)
- API: Đổi từ JSON sang multipart/form-data
- Validation: Hỗ trợ upload file (png, jpg, jpeg, svg, max 1MB)
- Storage: Icons lưu tại storage/app/public/category-icons/
- Response: API trả về icon_url thay vì icon_svg
- Service: Tự động xóa file cũ khi update/delete
- Tests: 22/22 passed ✓

NOTE: Postman collection cần update thủ công để dùng form-data thay vì JSON body cho Create/Update Category requests.
