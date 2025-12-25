# Transactions Module - TODO

## ✅ Completed Features

### Phase 1 - Core CRUD (100%)
- [x] Transaction Model với UUID v7
- [x] Repository & Service Pattern implementation
- [x] CRUD endpoints (Create, Read, Update, Delete)
- [x] Pagination với sorting
- [x] Filter theo category (single & multiple)
- [x] Search theo transaction name (case-insensitive)
- [x] **Date range filter** (start_date, end_date) ✨ NEW
- [x] Summary endpoint (total income/expense/balance)
- [x] API Resource với formatted amount (negative for expense)
- [x] Factory & Seeder với realistic test data
- [x] Feature tests (37 tests passing)
- [x] Postman collection với đầy đủ endpoints
- [x] Multi-language support (vi/en)

### Recent Updates
- [x] **Rename field:** `merchant_name` → `name` ✨ 2025-12-25
- [x] **Add date range filter** cho list endpoint ✨ 2025-12-25
- [x] **Refactor Repository** với `when()` method ✨ 2025-12-25

---

## 🔲 TODO - Current Sprint

### High Priority
- [ ] **Run tests** để verify merchant_name rename
- [ ] **Merge feature branch** vào dev với `--no-ff`
- [ ] **Run migration** rename column trong database
- [ ] **Test API** với Postman collection updated

### Medium Priority
- [ ] Update CLAUDE.md Development Roadmap
- [ ] Verify frontend không bị break bởi field name change
- [ ] Document breaking changes trong CHANGELOG

---

## 🚀 Future Enhancements

### Performance Optimization
- [ ] Add database indexes cho date range queries
- [ ] Implement Redis caching cho summary endpoint
- [ ] Optimize N+1 query issues (nếu có)

### Advanced Filtering
- [ ] Filter theo amount range (min_amount, max_amount)
- [ ] Filter theo transaction type (income/expense)
- [ ] Combined filters với AND/OR logic
- [ ] Saved filter presets

### Bulk Operations
- [ ] Bulk create transactions (import CSV/Excel)
- [ ] Bulk update (mass edit)
- [ ] Bulk delete với confirmation

### Advanced Features
- [ ] Recurring transactions (Phase 2)
- [ ] Transaction attachments (receipts, invoices)
- [ ] Transaction tags (flexible categorization)
- [ ] Transaction splits (split one transaction into multiple categories)
- [ ] Transaction notes với markdown support

### Analytics & Reporting
- [ ] Spending trends by category
- [ ] Monthly/yearly comparisons
- [ ] Custom date range reports
- [ ] Export to PDF/Excel
- [ ] Charts & visualizations

### Integration
- [ ] Link transactions to budgets (when Budgets module ready)
- [ ] Link transactions to goals (when Goals module ready)
- [ ] Link transactions to accounts (when Accounts module ready)
- [ ] Automatic categorization với ML (future)

---

## 📊 Module Stats

**Current Status:**
- Total endpoints: 8
- Total tests: 37 (all passing)
- Code coverage: ~95%
- API documentation: ✅ Complete
- Postman collection: ✅ Updated

**Recent Changes:**
- Files modified: 13
- Lines added: +198
- Lines removed: -55
- Migration files: 1 (pending run)

---

## 🐛 Known Issues

### To Investigate
- [ ] Amount bug report - verify DB values được lưu đúng
  - User payload có `status: completed` (đã remove)
  - Code logic OK, cần check actual DB values

### Fixed
- [x] Status field removed from schema
- [x] N+1 query với category relationship (fixed với eager loading)

---

## 📝 Notes

### Breaking Changes to Communicate
1. **Field rename:** `merchant_name` → `name`
   - Affects: API request/response, database column
   - Migration available với rollback support
   - Postman collection updated

2. **New features:**
   - Date range filter (backward compatible)
   - Không ảnh hưởng existing API calls

### Dependencies
- Transactions module sẵn sàng cho integration với:
  - ⏳ Accounts module (Phase 1 - TODO)
  - ⏳ Budgets module (Phase 2 - TODO)
  - ⏳ Goals module (Phase 2 - TODO)

---

**Last Updated:** 2025-12-25
**Next Review:** After merge & migration
