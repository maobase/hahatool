'use client';

import { useCallback, useEffect, useState } from 'react';
import { Loader2, MessageSquare, Send } from 'lucide-react';
import { fetchComments, postComment, type CommentItem } from '@/lib/client';
import { formatDate } from '@/lib/format';

function CommentNode({ comment, depth = 0 }: { comment: CommentItem; depth?: number }) {
  return (
    <li className={depth > 0 ? 'ml-8 mt-3' : ''}>
      <div className="rounded-xl bg-gray-50 px-4 py-3">
        <div className="flex items-center gap-2 text-xs text-gray-400">
          <span className="font-medium text-gray-700">{comment.author}</span>
          <time>{formatDate(comment.created)}</time>
        </div>
        <div
          className="prose prose-sm mt-1 max-w-none text-gray-700"
          dangerouslySetInnerHTML={{ __html: comment.text }}
        />
      </div>
      {comment.children && comment.children.length > 0 && (
        <ul>
          {comment.children.map((child) => (
            <CommentNode key={child.coid} comment={child} depth={depth + 1} />
          ))}
        </ul>
      )}
    </li>
  );
}

/** 工具评论区（基于 WordPress 评论系统） */
export default function Comments({ postId }: { postId: number }) {
  const [items, setItems] = useState<CommentItem[]>([]);
  const [count, setCount] = useState(0);
  const [author, setAuthor] = useState('');
  const [mail, setMail] = useState('');
  const [text, setText] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState<{ type: 'ok' | 'err'; text: string } | null>(null);

  const load = useCallback(async () => {
    try {
      const { count, items } = await fetchComments(postId);
      setCount(count);
      setItems(items);
    } catch {
      /* 评论加载失败不阻塞页面 */
    }
  }, [postId]);

  useEffect(() => {
    load();
  }, [load]);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (submitting) return;
    setMessage(null);
    setSubmitting(true);
    try {
      await postComment(postId, author.trim(), mail.trim(), text.trim());
      setText('');
      setMessage({ type: 'ok', text: '评论发表成功！' });
      await load();
    } catch (err) {
      setMessage({ type: 'err', text: err instanceof Error ? err.message : '提交失败，请稍后重试' });
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <section className="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8" aria-label="用户评论">
      <h2 className="flex items-center gap-2 text-lg font-bold text-gray-900">
        <MessageSquare size={18} className="text-brand-500" />
        用户评论
        <span className="text-sm font-normal text-gray-400">（{count}）</span>
      </h2>

      <form onSubmit={submit} className="mt-5 space-y-3">
        <div className="grid gap-3 sm:grid-cols-2">
          <div>
            <label htmlFor="c-author" className="mb-1 block text-xs font-medium text-gray-500">
              昵称 <span className="text-rose-500">*</span>
            </label>
            <input
              id="c-author"
              required
              maxLength={30}
              value={author}
              onChange={(e) => setAuthor(e.target.value)}
              className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-base outline-none transition focus:border-brand-400 sm:text-sm"
            />
          </div>
          <div>
            <label htmlFor="c-mail" className="mb-1 block text-xs font-medium text-gray-500">
              邮箱 <span className="text-rose-500">*</span>（不会公开）
            </label>
            <input
              id="c-mail"
              required
              type="email"
              autoComplete="email"
              value={mail}
              onChange={(e) => setMail(e.target.value)}
              className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-base outline-none transition focus:border-brand-400 sm:text-sm"
            />
          </div>
        </div>
        <div>
          <label htmlFor="c-text" className="mb-1 block text-xs font-medium text-gray-500">
            说说你的使用体验 <span className="text-rose-500">*</span>
          </label>
          <textarea
            id="c-text"
            required
            rows={3}
            maxLength={500}
            value={text}
            onChange={(e) => setText(e.target.value)}
            className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-base outline-none transition focus:border-brand-400 sm:text-sm"
          />
        </div>
        <div className="flex items-center gap-3">
          <button
            type="submit"
            disabled={submitting}
            className="inline-flex min-h-11 items-center gap-1.5 rounded-xl bg-brand-600 px-5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {submitting ? <Loader2 size={15} className="animate-spin" /> : <Send size={15} />}
            发表评论
          </button>
          {message && (
            <p role="status" className={`text-sm ${message.type === 'ok' ? 'text-emerald-600' : 'text-rose-500'}`}>
              {message.text}
            </p>
          )}
        </div>
      </form>

      {items.length > 0 && (
        <ul className="mt-6 space-y-3">
          {items.map((comment) => (
            <CommentNode key={comment.coid} comment={comment} />
          ))}
        </ul>
      )}
      {items.length === 0 && <p className="mt-6 text-sm text-gray-400">还没有评论，来抢沙发～</p>}
    </section>
  );
}
