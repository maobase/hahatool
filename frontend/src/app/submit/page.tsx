import type { Metadata } from 'next';
import { CheckCircle2, Mail, PencilLine } from 'lucide-react';
import { ADMIN_URL, SITE_NAME } from '@/lib/site';

export const metadata: Metadata = {
  title: '提交工具',
  description: `向 ${SITE_NAME} 提交你的 AI 工具，免费收录。`,
};

const CRITERIA = [
  '产品可正常访问、功能可用，非纯落地页',
  '与 AI 相关：大模型、AIGC、AI 工作流等',
  '提供清晰的产品介绍与定价信息',
  '无恶意行为（捆绑下载、欺诈营销等）',
];

export default function SubmitPage() {
  return (
    <div className="mx-auto max-w-3xl px-4 py-12 sm:px-6">
      <h1 className="text-2xl font-bold text-gray-900 sm:text-3xl">提交你的 AI 工具</h1>
      <p className="mt-3 leading-7 text-gray-600">
        {SITE_NAME} 欢迎开发者与厂商提交优秀的 AI 产品，审核通过后将免费收录，并有机会获得首页「编辑精选」与 Banner 推荐位展示。
      </p>

      <div className="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 className="font-semibold text-gray-900">收录标准</h2>
        <ul className="mt-4 space-y-3">
          {CRITERIA.map((c) => (
            <li key={c} className="flex items-start gap-2.5 text-sm leading-6 text-gray-600">
              <CheckCircle2 size={18} className="mt-0.5 shrink-0 text-emerald-500" />
              {c}
            </li>
          ))}
        </ul>
      </div>

      <div className="mt-6 grid gap-4 sm:grid-cols-2">
        <a
          href="mailto:submit@hahatool.local?subject=%E6%8F%90%E4%BA%A4AI%E5%B7%A5%E5%85%B7"
          className="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand-300 hover:shadow-md"
        >
          <span className="rounded-xl bg-brand-50 p-2.5 text-brand-600"><Mail size={20} /></span>
          <span>
            <span className="block font-semibold text-gray-900">邮件提交</span>
            <span className="mt-1 block text-sm leading-6 text-gray-500">
              发送工具名称、官网链接、一句话简介与定价模式，通常 3 个工作日内完成审核。
            </span>
          </span>
        </a>
        <a
          href={ADMIN_URL}
          target="_blank"
          rel="noopener noreferrer"
          className="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand-300 hover:shadow-md"
        >
          <span className="rounded-xl bg-brand-50 p-2.5 text-brand-600"><PencilLine size={20} /></span>
          <span>
            <span className="block font-semibold text-gray-900">运营后台录入</span>
            <span className="mt-1 block text-sm leading-6 text-gray-500">
              站点运营者可直接登录 Typecho 后台新增工具条目（详见《内容运营手册》）。
            </span>
          </span>
        </a>
      </div>
    </div>
  );
}
