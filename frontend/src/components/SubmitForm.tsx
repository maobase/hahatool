'use client';

import { useState } from 'react';
import { CheckCircle2, Loader2, Send } from 'lucide-react';

interface CategoryOption {
  mid: number;
  name: string;
}

const inputCls =
  'w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-base outline-none transition focus:border-brand-400 dark:border-gray-800 dark:bg-gray-800 sm:text-sm';

/** 工具提交表单：写入 WordPress 待审文章，运营审核后发布 */
export default function SubmitForm({ categories }: { categories: CategoryOption[] }) {
  const [form, setForm] = useState({ name: '', url: '', tagline: '', categoryId: '', pricing: '免费增值', mail: '', note: '' });
  const [submitting, setSubmitting] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState('');

  const set = (key: string) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) =>
    setForm((f) => ({ ...f, [key]: e.target.value }));

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (submitting) return;
    setError('');
    setSubmitting(true);
    try {
      const res = await fetch('/api/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const json = await res.json();
      if (!json.ok) throw new Error(json.message || '提交失败');
      setDone(true);
    } catch (err) {
      setError(err instanceof Error ? err.message : '提交失败，请稍后再试');
    } finally {
      setSubmitting(false);
    }
  };

  if (done) {
    return (
      <div className="flex flex-col items-center rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-12 text-center dark:border-emerald-900 dark:bg-emerald-900/20">
        <CheckCircle2 size={40} className="text-emerald-500" />
        <p className="mt-4 font-semibold text-gray-900 dark:text-gray-100">提交成功！</p>
        <p className="mt-2 max-w-sm text-sm leading-6 text-gray-600 dark:text-gray-400">
          你的工具已进入审核队列，通过后将自动出现在站内（通常 1-3 个工作日）。感谢贡献！
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
      <h2 className="font-semibold text-gray-900 dark:text-gray-100">在线提交</h2>
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label htmlFor="s-name" className="mb-1 block text-xs font-medium text-gray-500">
            工具名称 <span className="text-rose-500">*</span>
          </label>
          <input id="s-name" required maxLength={50} value={form.name} onChange={set('name')} className={inputCls} placeholder="如：Gemini" />
        </div>
        <div>
          <label htmlFor="s-url" className="mb-1 block text-xs font-medium text-gray-500">
            官网链接 <span className="text-rose-500">*</span>
          </label>
          <input id="s-url" required type="url" value={form.url} onChange={set('url')} className={inputCls} placeholder="https://…" />
        </div>
      </div>
      <div>
        <label htmlFor="s-tagline" className="mb-1 block text-xs font-medium text-gray-500">
          一句话简介 <span className="text-rose-500">*</span>（60 字内）
        </label>
        <input id="s-tagline" required maxLength={60} value={form.tagline} onChange={set('tagline')} className={inputCls} placeholder="谁 + 能干什么，例如：Google 出品的多模态 AI 助手" />
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        <div>
          <label htmlFor="s-cat" className="mb-1 block text-xs font-medium text-gray-500">
            分类 <span className="text-rose-500">*</span>
          </label>
          <select id="s-cat" required value={form.categoryId} onChange={set('categoryId')} className={inputCls}>
            <option value="">请选择</option>
            {categories.map((c) => (
              <option key={c.mid} value={c.mid}>{c.name}</option>
            ))}
          </select>
        </div>
        <div>
          <label htmlFor="s-pricing" className="mb-1 block text-xs font-medium text-gray-500">
            定价模式 <span className="text-rose-500">*</span>
          </label>
          <select id="s-pricing" required value={form.pricing} onChange={set('pricing')} className={inputCls}>
            <option value="免费">免费</option>
            <option value="免费增值">免费增值</option>
            <option value="付费">付费</option>
          </select>
        </div>
        <div>
          <label htmlFor="s-mail" className="mb-1 block text-xs font-medium text-gray-500">
            联系邮箱 <span className="text-rose-500">*</span>（不公开）
          </label>
          <input id="s-mail" required type="email" autoComplete="email" value={form.mail} onChange={set('mail')} className={inputCls} />
        </div>
      </div>
      <div>
        <label htmlFor="s-note" className="mb-1 block text-xs font-medium text-gray-500">
          补充说明（可选，500 字内）
        </label>
        <textarea id="s-note" rows={3} maxLength={500} value={form.note} onChange={set('note')} className={inputCls} placeholder="产品亮点、合作意向等" />
      </div>
      <div className="flex items-center gap-3">
        <button
          type="submit"
          disabled={submitting}
          className="inline-flex min-h-11 items-center gap-1.5 rounded-xl bg-brand-600 px-6 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {submitting ? <Loader2 size={15} className="animate-spin" /> : <Send size={15} />}
          提交审核
        </button>
        {error && (
          <p role="alert" className="text-sm text-rose-500">{error}</p>
        )}
      </div>
      <p className="text-xs leading-5 text-gray-400">
        提交即表示同意我们按收录标准审核；为防滥用，每小时最多提交 3 次。
      </p>
    </form>
  );
}
